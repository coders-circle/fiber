<?php

require_once 'SchemaVersions.php';
require_once 'config.php';

class Migration {

    public static function create_schema_versions_table() {
        $schm = new SchemaVersions();
        if ($schm->exists())
            return;

        Database::get_instance()->query_with_error(
            $schm->get_create_table_sql()
        );
    }

    public static function get_table_version($table_name) {
        self::create_schema_versions_table();
        $sv = SchemaVersions::query()->select("version")->
            where("table_name=?", $table_name)->first();
        if ($sv == null)
            return 0;
        return $sv->version;
    }

    public static function set_table_version($table_name, $version) {
        self::create_schema_versions_table();
        $sv = SchemaVersions::query()->
            where("table_name=?", $table_name)->first();
        if ($sv == null)
            $sv = new SchemaVersions();
        $sv->table_name = $table_name;
        $sv->version = $version;
        $sv->save();
    }

    public static function get_migration_version($table_name) {
        $max_version = 0;
        foreach(glob(ROOTDIR."/migrations/" . $table_name . "_*.sql") as $file) {
            $filename = basename($file, '.sql');
            $pattern = "/^" . $table_name . "_(?P<version>\d+)$/";
            if (preg_match($pattern, $filename, $matches)) {
                $version = intval($matches["version"]);
                $max_version = max($version, $max_version);
            }
        }
        return $max_version;
    }

    public static function get_migration($table_name, $version) {
        $filename = ROOTDIR."/migrations/" . $table_name . "_" . $version . ".sql";
        @$sql = file_get_contents($filename);
        return $sql;
    }

    public static function run_migrations($table_name) {
        $current = self::get_table_version($table_name);
        $latest = self::get_migration_version($table_name);

        if ($current >= $latest) {
            echo "No new migrations to apply !\n";
            return;
        }

        for ($i = $current+1; $i <= $latest; ++$i) {
            // get migration file for version 'i'
            $sql = self::get_migration($table_name, $i);
            if ($sql) {
                echo "Running sql:\n" . $sql . "\n";
                // run the migration sql
                Database::get_instance()->multi_query_with_error($sql);
                // update the version
                self::set_table_version($table_name, $i);
            }
        }
        echo "Done !\n";
    }
}

?>
