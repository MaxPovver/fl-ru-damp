<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/freelancers.common.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");


/**
 * Формирует список городов для выбранной страны.
 *
 * @param integer $country_id код страны
 * @param integer $city_id код города
 */
function ChangeCity($country_id, $city_id)
{
	$objResponse = new xajaxResponse();
//  $countries = country::GetCountries();
  $cities = city::GetCities($country_id);
  $select = "<select name=\"ff_city\" id=\"ff_city\" ";
  $select .= "style=\"width:254px;\">";
  $select .= "<option value=\"0\"";
  if ($city_id == 0)
  {
    $select .= " selected";
  }
  $select .= ">Все города</option>";
  if ($cities)
  {
    foreach ($cities as $id => $city)
    {
      $select .= "<option value=\"" . $id . "\"";
      if ($id == $city_id)
      {
        $select .= " selected";
      }
      $select .= ">" . $city . "</option>";
    }
    $select .= "</select>";
  }
	$objResponse->assign("city_select", "innerHTML", $select);
	return $objResponse;
}

function AddFav($frl_id, $prof_id, $is_pro = 'f')
{
  global $session;
  session_start();
  $uid = $_SESSION['uid'];
  $objResponse = &new xajaxResponse();
  $freelancer = &new freelancer();

  if ($uid && $frl_id && ($uid != $frl_id))
  {
    $info = $freelancer->ChangeFav($frl_id, $prof_id, $uid);
  }
  if (isset($info))
  {
// Временно отключено количество.    $objResponse->assign("fav_count", "innerHTML", $info[0]);
    if ($info[1])
    {
      if ($is_pro == 't')
      {
        $objResponse->assign("favstar_" . $frl_id, "src", '/images/ico_star_yellow_green.gif');
      }
      else
      {
        $objResponse->assign("favstar_" . $frl_id, "src", '/images/ico_star_yellow_grey.gif');
      }
    }
    else
    {
      if ($is_pro == 't')
      {
        $objResponse->assign("favstar_" . $frl_id, "src", '/images/ico_star_empty_green.gif');
      }
      else
      {
        $objResponse->assign("favstar_" . $frl_id, "src", '/images/ico_star_empty_grey.gif');
      }
    }
  }
  return $objResponse;
}

$xajax->processRequest();
?>
