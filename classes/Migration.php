<?php

require_once 'SchemaVersions.php';

class Migration {
    
    public static function get_table_version($table_name) {
        $sv = SchemaVersions::query()->select("version")->
            where("table_name=?", $table_name)->first();
        if ($sv == null)
            return 0;
        return $sv->version;
    }

    public static function set_table_version($table_name, $version) {
        $sv = new SchemaVersion();
        $sv->table_name = $table_name;
        $sv->version = $version;
        $sv->save();
    }

    public static function check_migration_file($table_name) {
        $max_version = 0;
        foreach(glob("../migrations/" . $table_name . "_*.sql") as $file) {
            $filename = basename($file, '.sql');
            $pattern = "/^" . $table_name . "_(?P<version>\d+)$/";
            if (preg_match($pattern, $filename, $matches)) {
                $version = intval($matches["version"]);
                $max_version = max($version, $max_version);
            }
        }
        return $max_version;
    }

    public static function run_migrations($table_name) {
        $current = self::get_table_version($table_name);
        $latest = self::check_migration_file($table_name);

        if ($current >= $latest)
            return;

        for ($i = $current+1; $i != $latest; ++$i) {
            // get migration file for version 'i'
            $filename = "../migrations/" . $table_name . "_" . $i . ".sql";
            $sql = file_get_contents($filename);
            if ($sql) {
                // run the migration sql
                Database::get_instance()->query_with_error($sql);

                // update the version
                set_table_version($table_name, $i);
            }
        }
    }
}

?>
