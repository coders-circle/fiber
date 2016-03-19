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

    public function process($data)
    {
        $content = file_get_contents(ROOTDIR.'/app/views/'.$this->file);

        // TODO: verbose, comments

        // regular expression for {% action bla-bla %} format.
        // The negative lookahead ((?!%).) gives any character except %}.
        // This rejects nested match.
        $action_regx = "[{%(((?!%}).)*)%}]";
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

                    case "url":
                        $content = str_replace($matches[0][$i], get_url($args[0]), $content);
                        ++$num_actions;
                        break;

                    // For each block
                    case "foreach":
                        $expression = str_replace("foreach ", "", $matches[1][$i]);
                        $replacement = "<?php foreach ($expression) { ?>";
                        $content = str_replace($matches[0][$i], $replacement, $content);
                        ++$num_actions;
                        break;

                    // If block
                    case "if":
                        $expression = str_replace("if ", "", $matches[1][$i]);
                        $replacement = "<?php if ($expression) { ?>";
                        $content = str_replace($matches[0][$i], $replacement, $content);
                        ++$num_actions;
                        break;

                    case "elseif":
                        $expression = str_replace("elseif ", "", $matches[1][$i]);
                        $replacement = "<?php } else if ($expression) { ?>";
                        $content = str_replace($matches[0][$i], $replacement, $content);
                        ++$num_actions;
                        break;

                    case "else":
                        $replacement = "<?php } else { ?>";
                        $content = str_replace($matches[0][$i], $replacement, $content);
                        ++$num_actions;
                        break;

                    // end blocks
                    case "endfor":
                    case "endif":
                        $replacement = "<?php } ?>";
                        $content = str_replace($matches[0][$i], $replacement, $content);
                        ++$num_actions;
                        break;

                    default:
                        // maybe throw some error
                        $replacement = "<?php " . $matches[1][$i] . "; ?>";
                        $content = str_replace($matches[0][$i], $replacement, $content);
                    }
                }
            }
        } while ($num_actions > 0);

        // regular expression for {{ var }} format.
        // The negative lookahead ((?!}}).) gives any character except }}.
        // This rejects nested matches.
        $var_regx = "[{{(((?!}}).)*)}}]";
        if(preg_match_all($var_regx, $content, $matches))
        {
            for($i=0; $i < count($matches[0]); $i++ )
            {
                $expression = trim($matches[1][$i]);
                $content = str_replace($matches[0][$i], "<?= $expression ?>", $content);
            }
        }


        $content = "?>".$content."<?php;";
        return run($content, $data);
    }
}

function run() {
    ob_start();
    extract(func_get_arg(1));
    eval(func_get_arg(0));
    return ob_get_clean();
}

?>
