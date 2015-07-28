<div style="<? print $style?> color:#000;"><?php    
    if (!$kind) { 
        if (($conted_prj["all"] == 0)&&($conted_prj["open"] == 0)&&($conted_prj["closed"] == 0)) {
                print "У $entity нет ни одного проекта";
        }elseif (($conted_prj["all"] != 0)&&($conted_prj["open"] == 0)) {
            print "У $entity нет открытых проектов";
        }elseif (($conted_prj["closed"] == 0)&&($_GET["closed"] == 1)) {
            print "У $entity нет закрытых проектов";
        }
    }    
    if ($kind == 1) {
        if (($conted_prj["all"] == 0)&&($conted_prj["open"] == 0)&&($conted_prj["closed"] == 0)) {
            print "У $entity нет ни одного проекта фри-ланс";
        }elseif (($conted_prj["all"] != 0)&&($conted_prj["open"] == 0)) {
            print "У $entity нет открытых проектов фри-ланс";
        }elseif (($conted_prj["closed"] == 0)&&($_GET["closed"] == 1)) {
            print "У $entity нет закрытых проектов фри-ланс";
        }
    }
    if ($kind == 2) {
        if (($conted_prj["all"] == 0)&&($conted_prj["open"] == 0)&&($conted_prj["closed"] == 0)) {
            print "У $entity нет ни одного конкурса";
        }elseif (($conted_prj["all"] != 0)&&($conted_prj["open"] == 0)) {
            print "У $entity нет открытых конкурсов";
        }elseif (($conted_prj["closed"] == 0)&&($_GET["closed"] == 1)) {
            print "У $entity нет закрытых конкурсов";
        }
    }
    if ($kind == 3) {
        if (($conted_prj["all"] == 0)&&($conted_prj["open"] == 0)&&($conted_prj["closed"] == 0)) {
            print "У $entity нет ни одного проекта в офис";
        }elseif (($conted_prj["all"] != 0)&&($conted_prj["open"] == 0)) {
            print "У $entity нет открытых проектов в офис";
        }elseif (($conted_prj["closed"] == 0)&&($_GET["closed"] == 1)) {
            print "У $entity нет закрытых проектов в офис";
        }
    }?></div>