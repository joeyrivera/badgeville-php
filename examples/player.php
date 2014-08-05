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

require_once 'config.php';

/** create **/
//$result = $site->players()->create([
//    'name' => 'Joey <asdf>  Tester8',
//    'email' => 'joeyrivera@air-watch.com8',
//    'sdfgs' => 'asdf'
//]);

/** update **/
//$result = $site->players()->find('53dbc9278803dad6a3000ffa');
//$result->display_name = 'testing5';
//$result->update(); //or $site->players()->update($player);

/** join team **/
//$result = $site->players('53e12cfd6173b18161002e2a')->joinTeams(['53e120c6c3fcd8440d002dc3','53e12012446f2dbe8d003309']);

/** leave team **/
//$result = $site->players('53e12cfd6173b18161002e2a')->leaveTeams(['53e120c6c3fcd8440d002dc3']);


/** create activity **/
//$result = $site->players('53dbc9278803dad6a3000ffa')->activities()->create(['verb' => 'logged in sdk']);

/** update activity history **/
$result = $site->players('53dbc9278803dad6a3000ffa')->activities('53e134a0c3fcd8b302002fb9')->updateHistory();

var_dump($result->toArray());