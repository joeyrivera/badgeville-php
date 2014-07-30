<?php

/* 
 * The MIT License
 *
 * Copyright 2014 Joey Rivera <joey1.rivera@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once '../vendor/autoload.php';

use Badgeville\Site;

// check for config file
if (!is_file('config.php')) {
    throw new Exception("The configuration file is missing. Create one based on the config.dist.php file and add the required information.");
}

// everything starts with a site, all other calls are related to the site
$site = new Site(require_once 'config.php');

$resources = [];
$data = [];

$path = dirname((__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Badgeville';

// create map of directory structure to render the menu dynamically
$dirArray = mapDir($path);

// do we have anything to load?
if (!empty($_GET['class'])) {
    $class = strtolower(trim($_GET['class']));
    $includes = !empty($_GET['includes']) ? ['includes' => strtolower(trim($_GET['includes']))] : [];
    
    unset($_GET['class']);
    unset($_GET['includes']);
    
    $params = [];
    
    // create an array of get params
    foreach ($_GET as $key => $value) {
        if (!empty($value)) {
            $params[substr(trim($key), -1)] = [
                'class' => substr(strtolower(trim($key)), 3, -2),
                'id' => $value
            ];
        }
    }
    
    if (count($params) == 0) {
        try {
            $call = '$site->'.$class.'()->findAll()';
            $items = $site->$class()->findAll();
            foreach ($items as $item) {
                $data[] = $item->toArray();
            }
        } catch (\Exception $e) {
            $data = $e->getMessage();
        }
    }

    try {
        $includesString = !empty($includes) ? '["includes" => ["'.  str_replace(',', '","', $includes['includes']).'"]]' : '[]';
        
        // ugle but quick and we know there are only up to 3 layers
        switch(count($params)) {
            case 1:
                $call = '$site->'.$class.'()->find("'.$params[0]['id'].'", '.$includesString.')->toArray()';
                $data = $site->$class()->find($params[0]['id'], $includes)->toArray();
                break;

            case 2:
                $call = '$site->'.$class.'("'.$params[0]['id'].'")->'.$params[1]['class'].'()->find("'.$params[1]['id'].'", '.$includesString.')->toArray()';
                $data = $site->$class($params[0]['id'])->$params[1]['class']()->find($params[1]['id'], $includes)->toArray();
                break;

            case 3:
                $call = '$site->'.$class.'("'.$params[0]['id'].'")->'.$params[1]['class'].'("'.$params[1]['id'].'")'
                    . '->'.$params[2]['class'].'()->find("'.$params[2]['id'].'", '.$includesString.')->toArray()';
                $data = $site->$class($params[0]['id'])->$params[1]['class']($params[1]['id'])
                    ->$params[2]['class']()->find($params[2]['id'], $includes)->toArray();
                break;

        }
    } catch (\Exception $e) {
        $data = $e->getMessage();
    }
}

function mapDir($path)
{
    $data = [];
    $dir = dir($path);
    
    while (false !== ($entry = $dir->read())) {
        if (is_file($path . DIRECTORY_SEPARATOR . $entry) && !isset($data[substr($entry, 0, -4)]) 
            && $entry != 'ResourceAbstract.php' && $entry != 'Site.php') {
            $data[substr($entry, 0, -4)] = [];
        } elseif (is_dir($path . DIRECTORY_SEPARATOR . $entry) && $entry != '.' && $entry != '..') {
            $data[$entry] = mapDir($path . DIRECTORY_SEPARATOR . $entry);
        }
    }  
    
    return $data;
}


function drawForms($resources)
{
    $html = '';
    foreach ($resources as $key => $value) {
        $html .= "<form>";
        $html .= addInputs($key, $value);
        $html .= "<label>Includes: <input type='text' name='includes'></label>";
        $html .= "<input type='hidden' name='class' value='{$key}'>";
        $html .= "<input type='submit'>";
        $html .= "</form>";
        $html .= "<hr />";
    }

    return $html;
}

function addInputs($parent, $child = [], $depth = 0)
{
    $indent = $depth * 50;
    $inputName = "id-{$parent}-{$depth}";
    $inputValue = !empty($_GET[$inputName]) ? $_GET[$inputName] : '';
    
    $html = " 
        <p style='margin-left:{$indent}px'>
            <label>{$parent}<br />
            <input type='text' name='{$inputName}' value='{$inputValue}'></label>
        </p>
    ";
    
    if (count($child) == 0) {
        return $html;
    }
            
    foreach ($child as $key => $value) {
        $html .= addInputs($key, $value, $depth+1);
    } 

    return $html;
}
?>

<h3>Available Resources</h3>
<p>click on the resource name to do a find all, else type in an id and click submit for find. You 
can also pass a comma delimited string for includes</p>
<div style="float:left;margin-right:20px"><?=drawForms($dirArray);?></div>

<!--<div>
    <h3>Params:</h3>
    <?=var_dump($_GET);?>
</div>-->

<div>
    <h3>Results <?=!empty($class)? "for {$class}" : '';?></H3>
    <p>php code:</p>
    <code><?=isset($call) ? $call : '';?></code>
    
    <p>data array:</p>
    <pre><?=var_dump($data);?></pre>
</div>

