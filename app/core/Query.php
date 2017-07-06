<?php

require_once 'Database.php';
require_once 'utils.php';

class Query
{
    protected $class_name;
    protected $projection;
    protected $selection;
    protected $order_by;
    protected $params = array();
    protected $ptypes = "";

    public function __construct($class_name) {
        $this->class_name = $class_name;
    }

    // Add selection query
    public function where() {
        $args = func_get_args();
        if (count($args) == 0)
            return $this;

        for($i=1; $i<count($args); ++$i) {
            $arg = $args[$i];
            $type = gettype($arg);

            if ($type == "string") {
                $this->ptypes .= 's';
                $this->params[] = $arg;
            }
            else if ($arg instanceof DateTime) {
                $this->ptypes .= 's';
                $this->params[] = $arg->format("Y-m-d H:i:s");
            }
            else {
                $this->ptypes .= 'd';
                $this->params[] = $arg;
            }
        }

        $selection = '(' . $args[0] . ')';

        if ($this->selection) {
            $this->selection .= ' AND ' . $selection;
        } else {
            $this->selection = $selection;
        }
        return $this;
    }

    // Add order by
    public function order_by($order_by) {
        $this->order_by = $order_by;
        return $this;
    }

    // Add projection query
    public function select() {
        $args = func_get_args();
        foreach ($args as $arg) {
            if ($this->projection != "")
                $this->projection .= ",";
            $this->projection .= $arg;
        }
        return $this;
    }

    // Run query
    public function get() {
        $db = Database::get_instance();
        $table = call_user_func(array($this->class_name, "get_table_name"));

        $projection = "*";
        if ($this->projection)
            $projection = $this->projection;

        $selection = "";
        if ($this->selection)
            $selection = "WHERE " . $this->selection;

        $extra = "";
        if ($this->order_by)
            $extra .= "ORDER BY " . $this->order_by;

        $sql = "SELECT $projection FROM $table $selection $extra";
        $stmt = $db->prepare($sql);
        if (!$stmt)
            die($db->error . "<br> $sql");

        $ptypes = $this->ptypes;
        $params = array();

        $params[] = &$ptypes;
        for ($i=0; $i<count($this->params); ++$i)
            $params[] = &$this->params[$i];

        if (count($params) > 1)
            call_user_func_array(array($stmt, "bind_param"), $params);

        $stmt->execute();
        $result = $stmt->get_result();

        //return $class_name::get_from_query_result($result);
        return call_user_func(array($this->class_name, "get_from_query_result"), $result);
    }

    // Delete using given selection.
    public function delete() {
        $db = Database::get_instance();
        $table = call_user_func(array($this->class_name, "get_table_name"));

        $selection = "";
        if ($this->selection)
            $selection = "WHERE " . $this->selection;

        $sql = "DELETE FROM $table $selection";
        $stmt = $db->prepare($sql);
        if (!$stmt)
            die($db->error . "<br> $sql");

        $ptypes = $this->ptypes;
        $params = array();

        $params[] = &$ptypes;
        for ($i=0; $i<count($this->params); ++$i)
            $params[] = &$this->params[$i];

        if (count($params) > 1)
            call_user_func_array(array($stmt, "bind_param"), $params);

        $stmt->execute();
        $result = $stmt->get_result();
    }

    public function first() {
        $objects = $this->get();
        if (count($objects)==0)
            return null;
        return $objects[0];
    }

    // TODO aggregates
}

?>
