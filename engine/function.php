<?

	function paginator($page, $pages, $count=PAGINATOR_PAGES_COUNT, $href=false) {
		if($pages==1) {return '';}
	    $html = '<div class="pager" >';
        
        $start = $page - $count;
        if($start<1) $start = 1;
        
        $end = $page + $count;
        if($end>$pages) $end = $pages;
        
        if($page < $pages) {$html .= sprintf($href, '<span class="page-next"><a href=', $page+1, '>следующая</a>&nbsp;&nbsp;&rarr;</span>');} 
        if($page > 1) {$html .= sprintf($href, '<span class="page-back">&larr;&nbsp;&nbsp;<a href=', $page-1 ,'>предыдущая</a></span>');} //$page-1
        //$page+1
        
        for($i=$start;$i<=$end;$i++) {
            if($i == $start && $start > 1) {  $html .= sprintf($href, '<a href="', 1 ,'">1</a>&nbsp;');  if($i==3) $html .= sprintf($href, '<a href="', 2 ,'">2</a>&nbsp;'); elseif($i!=2) $html .= "&nbsp;..&nbsp;&nbsp;";}
            $html .= ($page == $i? '<span class="page"><span><span>'.$i.'</span></span></span>&nbsp;' : sprintf($href, '<a href=', $i ,'>'.$i.'</a>&nbsp;'));
            if($i == $end && $page < $pages-1 && $pages > $end ) { if($pages-$end-1 == 1) $html .= sprintf($href, '<a href="', $pages-1 ,'">'.($pages-1).'</a>&nbsp;'); elseif($pages-$end-1 > 1) $html .= "..&nbsp;"; $html .= sprintf($href, '<a href="', $pages ,'">'.$pages.'</a>&nbsp;');}
        } 
        
        return $html.'</div>';   
    }

    function br2nl($string) {
       $string = preg_replace("/(\r\n|\n|\r)/", "", $string);
       return preg_replace("=<br */?>=i", "\n", $string);
    }
    
    function getHistoryIco($code) {
    	switch($code) {
    		case 16:
    		case 17:
    		case 18:
    		case 24:
    		case 25:
    		case 26:
    		case 27:
    		case 34:
    		case 35:
    		case 39:
    		case 42:
    		case 52:
    		case 66:
    		case 67:
    		case 68:						
    			$ret = "/images/gift.gif";
    			break;
    		case 12:
    			$ret = "/images/plus_green.gif";	
    		case 51: 
    			$ret = "/images/minus.gif";
    			break;
    		case -23:
    			$ret = "/images/arrow_right_red.gif";
    			break;
    		case 23:
    			$ret = "/images/arrow_left_blue.gif";
    			break;		
    		default:
    			$ret = "/images/minus.gif";
    			break;
    	}
    	
    	return $ret;
    }
    
    function getSortStatus($link1=null, $link2=null, $status=false) {
    	if($status == 1) {
    		echo '<img src="/images/arrow-bottom-a.png" alt="" width="11" height="11" /> <a href="'.$link2.'" style="text-decoration:none;"><img src="/images/arrow-top.png" alt="" width="11" height="11" /></a>';		
    	} elseif($status==2) {
    		echo '<a href="'.$link1.'" style="text-decoration:none;"><img src="/images/arrow-bottom.png" alt="" width="11" height="11" /></a> <img src="/images/arrow-top-a.png" alt="" width="11" height="11" />';
    	} else {
    		echo '<a href="'.$link1.'" style="text-decoration:none;"><img src="/images/arrow-bottom.png" alt="" width="11" height="11" /></a> <a href="'.$link2.'"><img src="/images/arrow-top.png" alt="" width="11" height="11" /></a>';
    	}
    	
    }
?>