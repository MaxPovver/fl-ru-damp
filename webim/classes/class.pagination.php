<?php
/* 
 * 
 * Данный файл является частью проекта Веб Мессенджер.
 * 
 * Все права защищены. (c) 2005-2009 ООО "ТОП".
 * Данное программное обеспечение и все сопутствующие материалы
 * предоставляются на условиях лицензии, доступной по адресу
 * http://webim.ru/license.html
 * 
 */
?>
<?php

define('PAGINATION_SPACING', "&nbsp;&nbsp;&nbsp;");
define('LINKS_ON_PAGE', 5);

/**
 * Готовит данные для построения постраничного вывода
 * 
 * @param  int $items_cnt
 * @param  int $default_items_per_page
 * @return array
 */
function setup_pagination_cnt( $items_cnt, $default_items_per_page = 15 ) {
  $pagination = array();

  if ( !empty($items_cnt) ) {
    $items_per_page = verify_param( 'items', '/^\d{1,3}$/', $default_items_per_page );
    
    if ( $items_per_page < 2 ) {
        $items_per_page = 2;
    }

    $total_pages = div( $items_cnt + $items_per_page - 1, $items_per_page );
    $curr_page   = verify_param( 'page', '/^\d{1,6}$/', 1 );

    if ( $curr_page < 1 ) {
        $curr_page = 1;
    }
    
    if ( $curr_page > $total_pages ) {
        $curr_page = $total_pages;
    }

    $start_index = ($curr_page - 1) * $items_per_page;
    $end_index   = min( $start_index + $items_per_page, $items_cnt );
    
    $pagination = array(
        'page'  => $curr_page, 
        'items' => $items_per_page, 
        'total' => $total_pages, 
        'count' => $items_cnt, 
        'start' => $start_index, 
        'end'   => $end_index
    );
  }

  return $pagination;
}

function setup_pagination($items, $default_items_per_page = 15) {
  $pagination = array();

  if (!empty($items)) {
    $items_per_page = verify_param("items", "/^\d{1,3}$/", $default_items_per_page);
    if ($items_per_page < 2)
    $items_per_page = 2;

    $total_pages = div(count($items) + $items_per_page - 1, $items_per_page);
    $curr_page = verify_param("page", "/^\d{1,6}$/", 1);

    if ($curr_page < 1)
    $curr_page = 1;
    if ($curr_page > $total_pages)
    $curr_page = $total_pages;

    $start_index =($curr_page-1)*$items_per_page;
    $end_index = min($start_index+$items_per_page, count($items));
    $pagination['pagination_items'] = array_slice($items, $start_index, $end_index-$start_index);
    $pagination['pagination'] =
    array("page" => $curr_page, "items" => $items_per_page, "total" => $total_pages,
    "count" => count($items), "start" => $start_index, "end" => $end_index);
  }

  return $pagination;
}

function generate_pagination_link($page, $title) {
  $lnk = $_SERVER['REQUEST_URI'];
  $href = preg_replace("/\?page=\d+\&/", "?", preg_replace("/\&page=\d+/", "", $lnk));
  $href .= strstr($href, "?") ? "&page=".$page : "?page=".$page;
  return "<a href=\"$href\" class=\"pagelink\">$title</a>";
}

function generate_pagination_image($id) {
  return "<img src=\"".WEBIM_ROOT."/images/$id.png\" border=\"0\"/>";
}

function generate_pagination($pagination) {
  $result = Resources::Get("tag.pagination.info",
  array($pagination['page'], $pagination['total'], $pagination['start']+1, $pagination['end'], $pagination['count']))."<br/>";

  if ($pagination['total'] > 1) {
    $result.="<br/><div class='pagination'>";
    $curr_page = $pagination['page'];

    $minPage = max($curr_page - LINKS_ON_PAGE, 1);
    $maxPage = min($curr_page + LINKS_ON_PAGE, $pagination['total']);

    if ($curr_page > 1) {
      $result .= generate_pagination_link($curr_page-1, generate_pagination_image("prevpage")).PAGINATION_SPACING;
    }

    for ($i = $minPage; $i <= $maxPage; $i++) {
      $title = abs($curr_page-$i) >= LINKS_ON_PAGE && $i != 1 ? "..." : $i;
      if ($i != $curr_page)
      $result .= generate_pagination_link($i, $title);
      else
      $result .= "<span class=\"pagecurrent\">$title</span>";
      if ($i < $maxPage)
      $result .= PAGINATION_SPACING;
    }


    if ($curr_page < $pagination['total']) {
      $result .= PAGINATION_SPACING.generate_pagination_link($curr_page+1, generate_pagination_image("nextpage"));
    }
    $result.="</div>";
  }
  return $result;
}

?>