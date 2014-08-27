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
//    'email' => '22293@airwatchuser.com'
//]);

$playerId = '53f76205e800d5f9e800177a';

/** update **/
//$result = $site->players()->find($playerId);
//$result->display_name = 'testing5';
//$result->update(); //or $site->players()->update($player);

/** join team **/
//$result = $site->players($playerId)->joinTeams(['53e120c6c3fcd8440d002dc3','53e12012446f2dbe8d003309']);

/** leave team **/
//$result = $site->players($playerId)->leaveTeams(['53e120c6c3fcd8440d002dc3']);

/** create activity, bring back rewards and missions that might have been trigger by this call **/
//$result = $site->players($playerId)->activities()->create('testing-update', ['includes' => 'rewards', 'fields' => 'custom']);
//$result = $site->players($playerId)->activities()->create(['verb' => 'testing-update', 'device' => 'myphone'], ['includes' => 'rewards', 'fields' => 'custom']);

/** update activity history **/
//$result = $site->players($playerId)->activities('53f7a411c3fcd80515001e98')->updateHistory();
// or create activity then call
//$result->updateHistory();

/** get latest activity **/
//$result = $site->players($playerId)->activities()->findAll([
//    'verb' => 'newmeta',
//    'includes' => 'rewards,missionhistories', 
//    'limit' => 30, 
//    'fields' => 'history'
//]);

/** get rewards by category **/
$result = $site->rewards()->findAll([
    'category' => 'asp',
    'fields' => 'category'
]);

var_dump($result->toArray());