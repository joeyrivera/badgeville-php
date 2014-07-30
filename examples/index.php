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
    
    // figure out based on params if we need to call find or findall
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

    // need to call find, figure out on which resource
    try {
        // to display the php code in the screen
        $includesString = !empty($includes) ? '["includes" => ["'.  str_replace(',', '","', $includes['includes']).'"]]' : '[]';
        
        // ugle but quick and we know there are only up to 3 layers
        switch(count($params)) {
            case 1:
                // to display the php code in the screen
                $call = '$site->'.$class.'()->find("'.$params[0]['id'].'", '.$includesString.')->toArray()';
                $data = $site->$class()->find($params[0]['id'], $includes)->toArray();
                break;

            case 2:
                // to display the php code in the screen
                $call = '$site->'.$class.'("'.$params[0]['id'].'")->'.$params[1]['class'].'()->find("'.$params[1]['id'].'", '.$includesString.')->toArray()';
                $data = $site->$class($params[0]['id'])->$params[1]['class']()->find($params[1]['id'], $includes)->toArray();
                break;

            case 3:
                // to display the php code in the screen
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

/**
 * create a multi-dimensional view of the folder structure
 * 
 * @param string $path
 * @return array
 */
function mapDir($path)
{
    $data = [];
    $dir = dir($path);
    
    // if file map, else call recursively
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

/**
 * For each resource, create a form
 * 
 * @param array $resources
 * @return string
 */
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
        $html .= "<hr style='margin-bottom:40px;' />";
    }

    return $html;
}

/**
 * Create all needed inputs for each resource based on hierarchy
 * 
 * @param string $parent
 * @param array $child
 * @param int $depth
 * @return string
 */
function addInputs($parent, $child = [], $depth = 0)
{
    $indent = $depth * 50;
    
    // the naming convension allows us to extract which methods to call later
    // parent is the resource name and depth gives us an idea of which parent
    // or child ids are required.
    $inputName = "id-{$parent}-{$depth}";
    $inputValue = !empty($_GET[$inputName]) ? $_GET[$inputName] : '';
    
    $html = " 
        <p style='margin-left:{$indent}px'>
            <label>{$parent}<br />
            <input type='text' name='{$inputName}' value='{$inputValue}'></label>
        </p>
    ";

    // if no child then done
    if (count($child) == 0) {
        return $html;
    }
    
    // call recursively until we have no more children
    foreach ($child as $key => $value) {
        $html .= addInputs($key, $value, $depth+1);
    } 

    return $html;
}
?>
<h3>Find/FindAll Utility</h3>
<p>
    This utility allows you to do find and find all calls to the library by interacting
    with the forms. To find all, click on submit on the resource block you are interested 
    in without filling out any input boxes.
</p>

<p>
    To do a find, simply type the id into the corresponding input box and click submit. For 
    example, if you want to find a player who's id is '123', type '123' next to the players 
    input bot and hit submit.
</p>

<p>
    Resources have a parent child relationship, to find a specific reward for a player, you 
    first need to have the player id. In this scenario you can do a find all on players, find 
    the id of the player you want and type that id in the players input box. Next type 'rewards' 
    into the includes box, and click submit. This should return the player with his/her rewards. 
    Now you can copy/paste the reward id from the results into the rewards input box under 
    players and hit submit. You should see the individual reward listed in the results panel now.
</p>

<p>
    The includes input box allows you to type a comman delimited string to bring back all those 
    resources back with the call. You can view the Badgeville documentation to see which resource 
    can include what child resources. For example, if you want a player of id '123' to come 
    back with rewards, activities, and missions type '123' in the player input box, and in 
    the includes box type 'rewards,activities,missions' then click submit. Your result should 
    be the player with all those includes resources.
</p>

<p>Finally, each calll will display the php code needed to replicate to help the user understand 
    how this library is used behind the scense. As long as you create a config.php file in the 
    examples folder and add your api key and site id, you should be able to use this utility.
</p>

<div style="float:left;margin-right:20px">
    <h3>Resources</h3>
    <?=drawForms($dirArray);?>
</div>

<!--<div>
    <h3>Params:</h3>
    <?=var_dump($_GET);?>
</div>-->

<div>
    <h3>Results <?=!empty($class)? "for {$class}" : '';?></h3>
    <p>php code:</p>
    <code><?=isset($call) ? $call : '';?></code>
    
    <p>data array:</p>
    <pre><?=var_dump($data);?></pre>
</div>

