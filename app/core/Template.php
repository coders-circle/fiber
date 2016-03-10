<?php

require_once 'config.php';

class Template
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    private function include_to($content, $match, $template_file_name)
    {
        $include_content = file_get_contents(ROOTDIR.'/app/views/'.$template_file_name);
        return str_replace($match, $include_content, $content);
    }

    // extend is a bit complex, so is left for later
    private function extend_to($content, $match, $base_template_name)
    {
        $base = file_get_contents(ROOTDIR.'/views/'.$base_template_name);
        //$block_regx = "[\{\%block (.*)\%\}(.*)\{\% endblock \%\}]";
        $block_regx = "#{% *insert.(.*)%}#";
        $blocks = array();
        if(preg_match_all($block_regx, $base, $matches))
        {
            for($i=0; $i<count($matches[1]); $i++)
            {
                array_push($blocks, $matches[1][$i]);
            }
        }
        var_dump($blocks);
    }

    public function process($data)
    {
        $content = file_get_contents(ROOTDIR.'/app/views/'.$this->file);

        // regular expression for {% action bla-bla %} format
        $action_regx = "[{%(.*)%}]";
        $num_actions = 0;
        do
        {
            $num_actions = 0;
            if(preg_match_all($action_regx, $content, $matches))
            {
                for($i=0; $i<count($matches[0]);$i++)
                {
                    $command = trim($matches[1][$i]);
                    $temp = explode(' ', $command, 2);
                    $action = $temp[0];
                    $args = array_slice($temp, 1);

                    // remove quotes if present
                    for( $n=0; $n<count($args); $n++)
                    {
                        $args[$n] = trim($args[$n], '"');
                        $args[$n] = trim ($args[$n], "'");
                    }

                    switch ($action)
                    {
                    case "include":
                        $content = $this->include_to($content, $matches[0][$i], $args[0]);
                        ++$num_actions;
                        break;
                    // yet to be implemented
                    // case "extends":
                    //     $this->extend_to($content, $matches[0][$i], $args[0]);
                    //     $content = str_replace($matches[0][$i], "", $content);
                    //     break;
                    case "url":
                        $content = str_replace($matches[0][$i], get_url($args[0]), $content);
                        ++$num_actions;
                        break;
                    default:
                        // maybe throw some error
                        $content = str_replace($matches[0][$i], "", $content);
                    }
                }
            }
        } while ($num_actions > 0);

        // regular expression for {{ var }} format
        $var_regx = "[{{(.*)}}]";
        if(preg_match_all($var_regx, $content, $matches))
        {
            for($i=0; $i < count($matches[0]); $i++ )
            {
                $key = trim($matches[1][$i]);
                $content = str_replace($matches[0][$i],
                    array_key_exists($key, $data)? $data[$key] : "",
                    $content);
            }
        }
        return $content;
    }
}

?>
