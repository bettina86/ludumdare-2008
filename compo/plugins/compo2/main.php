<?php
/**
 * Convert some attrs ' x="5" y="3" j="n" ' to an array.  Must have quotes.
 * @param attrs
 * @return array
 */
function xmlhack_attrs2array($data) {
    $r = array();
//     preg_match_all("/([a-zA-Z0-9_]+)\s*=\s*[']([^']*)[']/s",$data,$m);
//     foreach ($m[1] as $n=>$k) { $r[$k] = $m[2][$n]; }
    preg_match_all("/([a-zA-Z0-9_]+)\s*=\s*[\"]([^\"]*)[\"]/s",$data,$m);
    foreach ($m[1] as $n=>$k) { $r[$k] = $m[2][$n]; }
    return ($r);
}


function _compo2_main($m) {
    /* old parsing
    $parts = explode(":",html_entity_decode($m[1]));
    $state = $parts[0];
    $jcat = $parts[1];
    $opts = $parts[2];
    $opts = explode(";",str_replace(" ","",$opts));
    */
    
    /** Params **
    @cats   List of judging categories: "Innovation;Fun;Production"
    @jcat   Wordpress Journal category
    @state  State of compo (active,rate,results,closed)
    */
    
    $params = xmlhack_attrs2array($m[1]);
    
    @$params["init"] = intval($params["init"]);
    
    if ($params["init"] == 0) {
        $params["divs"] = "compo";
        if (isset($params["gamejam"])) {
            $params["divs"] .= ";gamejam";
        }
        $params["compo_cats"] = $params["cats"];
        $params["compo_title"] = "Competition";
        $params["compo_summary"] = "My entry follows all the rules and I want it to be judged.";
        $params["compo_link"] = "#";
        $params["gamejam_title"] = "Game Jam";
        $params["gamejam_summary"] = "My entry doesn't follow the rules or I don't want it to be judged.";
    }
    if (!isset($params["opendivs"])) { $params["opendivs"] = $params["divs"]; }
    $params["divs"] = explode(";",str_replace(" ","",$params["divs"]));
    $params["opendivs"] = explode(";",str_replace(" ","",$params["opendivs"]));
    if ($params["locked"]) { $params["opendivs"] = array(); }

    $cats = array();
    foreach ($params["divs"] as $div) {
        if (isset($params["{$div}_cats"])) {
            $params["{$div}_cats"] = explode(";",str_replace(" ","",$params["{$div}_cats"]));
            foreach ($params["{$div}_cats"] as $v) {
                if (!in_array($v,$cats)) { $cats[]= $v; }
            }
        }
    }

    $params["cats"] = $cats;
    if (!isset($params["topcat"])) { $params["topcat"] = "Overall"; }
    foreach (array("calc_droplow"=>0,"calc_drophigh"=>0,"calc_reqvote"=>5) as $k=>$v) {
        $params[$k] = isset($params[$k])?intval($params[$k]):$v;
    }
    
    // some other auto-calculated stuff
    // @cat Contenst id (taken from page ID)
    $params["cid"] = intval($GLOBALS["post"]->ID);
    
    // @uid User ID
    // @user WP-User object
    $user = wp_get_current_user(); $uid = $user->ID;
    $params["uid"] = $uid;
    $params["user"] = $user;
    
    // @has_entry True if the current user has an entry in this compo
    $ce = compo2_entry_load($pid,$uid);
    $params["has_entry"] = ($ce["id"]!=0);
    
    // State is changed to admin if ?admin=1 is in the URL
    if ($_REQUEST["admin"]) { $params["state"] = "admin"; }
    
    // State is changed to misc, if we're accessing a misc_ page
    $action = isset($_REQUEST["action"])?$_REQUEST["action"]:"default";
    if (in_array($action,array("misc_links"))) {
        $params["state"] = "misc";
    }
    
    // If we're in debug mode, display our params
//     if (strlen($_REQUEST["debug"])) { echo "<pre>";print_r($params);echo "</pre>"; }
    
    // dispatch according to the current state
    ob_start();
    $state = $params["state"];
    if ($state == "active") { _compo2_active($params); }
    elseif ($state == "rate") { _compo2_rate($params); }
    elseif ($state == "results") { _compo2_results($params); }
    elseif ($state == "admin") { _compo2_admin($params); }
    elseif ($state == "misc") { _compo2_misc($params); }
    elseif ($state == "closed") { _compo2_closed($params); }
    else { compo2_error("compo2 - Invalid state: $state"); }
//     $user = compo2_get_user($params["uid"]);
    if ($user->user_level >= 10) {
        echo "<p><a href='?admin=1'>Enter admin mode</a></p>";
    }
    $r = ob_get_contents();
    ob_end_clean();
    
    if (defined("W3TC_DIR")) { 
        include_once W3TC_DIR . '/lib/W3/PgCache.php';
        $w3_pgcache = & W3_PgCache::instance();
        $w3_pgcache->flush();
    }
    
    // output the content
    return "<div id='compo2'>$r</div>";
}


function compo2_main($content) {
    $content = preg_replace_callback("/\[compo2\s(.*?)\]/","_compo2_main",$content);
    return $content;
}
?>