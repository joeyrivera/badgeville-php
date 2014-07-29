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

$resources = [];
$data = [];

// get all root nodes and add links for all
$path = dirname((__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Badgeville';
$dir = dir($path);
while (false !== ($entry = $dir->read())) {
    if (!is_file($path . DIRECTORY_SEPARATOR . $entry) || strpos($entry, 'Abstract') !== false || strpos($entry, 'Site') !== false) {
        continue;
    }
    $resources[] = substr($entry, 0, -4);
}

// do we have anything to load?
if (!empty($_GET['class'])) {
    $class = strtolower(trim($_GET['class']));
    $id = !empty($_GET['id']) ? trim($_GET['id']) : null;
    $includes = !empty($_GET['includes']) ? ['includes' => strtolower(trim($_GET['includes']))] : [];
    
    // check for config file
    if (!is_file('config.php')) {
        throw new Exception("The configuration file is missing. Create one based on the config.dist.php file and add the required information.");
    }

    $site = new Site(require_once 'config.php');

    if (!empty($id)) {
        $data = $site->$class()->find($id, $includes)->toArray();
    } else {
        $items = $site->$class()->findAll();
        foreach ($items as $item) {
            $data[] = $item->toArray();
        }
    }
}
?>

<h3>Available Resources</h3>
<p>click on the resource name to do a find all, else type in an id and click submit for find. You 
can also pass a comma delimited string for includes</p>
<?php foreach ($resources as $resource) :?>
<form>
    <a href="?class=<?=$resource?>"><?=$resource?></a> 
    <label>Id</label><input type="text" name="id">
    <label>Includes</label><input type="text" name="includes">
    <input type="hidden" name="class" value="<?=$resource?>">
    <input type="submit">
</form>
<?php endforeach; ?>

<h3>Results <?=!empty($class)? "for {$class}" : '';?></H3>
<pre><?=var_dump($data);?>
</pre>