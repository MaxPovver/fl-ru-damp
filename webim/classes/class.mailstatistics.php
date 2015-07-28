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

require_once('functions.php');
require_once('class.thread.php');
require_once('class.operator.php');
require_once('class.smartyclass.php');


class MailStatistics {
  const FIELD_LENGTH = 14;

  public static function getText() {
    $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

    $tomorrow = time() + 24 * 60 * 60;
    $end = mktime(0, 0, 0, date('m', $tomorrow), date('d', $tomorrow), date('Y', $tomorrow));

    $text = Resources::Get('mail.statistics.subject.head')."\n\n";
    $text .= self::formatStatistics($start, $end) . "\n\n";
    $text .= self::formatThreads($start, $end);

    return $text;
  }

  public static function sendStatsIfNeeded($filename, $hour) {
    return  false;
//    if(file_exists($filename)) {

//      if(!$fd = fopen($filename,"r+")) {

//        return false;
//      }
//
//      if(!flock($fd, LOCK_EX)) {

//        fclose($fd);
//        return false;
//      }
//      $ts = intval(fgets($fd));

//    } else {

//      $ts = 0;
//      if(!$fd = fopen($filename,"w")) {

//        return false;
//      }
//
//      if(!flock($fd, LOCK_EX)) {

//        fclose($fd);
//        return false;
//      }
//    }
//
//    $launch_ts = mktime(0, 0, 0, @date('m'), @date('d'), @date('Y')) + $hour * 60 * 60;
//    // avoid empty time zone

//
//    if($launch_ts <= $ts) {

//      flock($fd, LOCK_UN);
//      fclose($fd);
//      return false;
//    }
//
//    if(!ftruncate($fd, 0)) {

//      flock($fd, LOCK_UN);
//      fclose($fd);
//      return false;
//    }
//

//
//
//    $subject = Resources::Get("mail.statistics.subject", array(date(getDateFormat(), $launch_ts)));
//
//    if(!self::sendStats(Settings::Get('stats_email'), Settings::Get('from_email'), $subject, self::getText())) {

//      flock($fd, LOCK_UN);
//      fclose($fd);
//      return false;
//    }
//

//
//    if(!fwrite($fd, $launch_ts)) {

//    }
//

//    flock($fd, LOCK_UN);
//    fclose($fd);
//
//    return true;
  }

  private static function sendStats($toaddr, $reply_to, $subject, $body) {
    if (WEBIM_ENCODING != MAIL_ENCODING) {
      $reply_to = smarticonv(WEBIM_ENCODING, MAIL_ENCODING, $reply_to);
      $body = smarticonv(WEBIM_ENCODING, MAIL_ENCODING, $body);
      $subject = smarticonv(WEBIM_ENCODING, MAIL_ENCODING, $subject);
    }

    $headerCharset = MAIL_ENCODING == 'CP1251' ? 'windows-1251' : 'UTF-8';

    $headers = "From: ".encodeForEmailAddress($reply_to, MAIL_ENCODING)."\r\n"
            ."Reply-To: ".encodeForEmailAddress($reply_to, MAIL_ENCODING)."\r\n"
            ."Content-Type: text/html; charset=\"".$headerCharset."\"\r\n"
            .'X-Mailer: PHP/'.phpversion();

    $real_subject = encodeForEmail($subject, MAIL_ENCODING);
    $body = "<pre>".$body."</pre>";








    return mail(encodeForEmailAddress($toaddr, MAIL_ENCODING), $real_subject, $body, $headers);
  }

  private static function formatThreads($start, $end) {
    $threads = MapperFactory::getMapper("Thread")->enumByDate($start, $end);
    $result = "";
    foreach ($threads as $thread) {
      $result .= "#".$thread['threadid']. " " . date(getDateTimeFormat(), $thread['created']) . "\n";
      $result .= "-----------------------------------------------\n";
      $lastid = "-1";
      $result .= implode("\n", Thread::getInstance()->GetMessages($thread['threadid'], "text", true, $lastid, true));
      $result .= "-----------------------------------------------\n\n\n\n";
    }

    return $result;
  }

  private static function formatStatistics($start, $end) {
    $result = "";

    $report = MapperFactory::getMapper("Thread")->getReportByDate($start, $end);

    if(count($report) == 0) {
      $report = array(array(date(getDateFormat(), $start), 0, 0, 0));
    }

    if(count($report) > 1) {
      $report_total = MapperFactory::getMapper("Thread")->getReportTotalByDate($start, $end);
      $report_total = array_values($report_total);
      array_unshift($report_total, Resources::Get('report.total'));
      $report[] = $report_total;
      unset($report_total);
    }

    $result .= Resources::Get("report.bydate.title") . "\n";
    $result .= self::formatTable(
            array(
            Resources::Get("report.bydate.1"),
            Resources::Get("report.bydate.2"),
            Resources::Get("report.bydate.3"),
            Resources::Get("report.bydate.4")
            ),
            $report,
            self::FIELD_LENGTH
            ) . "\n\n";

    $report = MapperFactory::getMapper("Operator")->getAdvancedReportByDate($start, $end);

    $result .= Resources::Get('report.byoperator_date.title') . "\n";
    $result .= Resources::Get('report.no_items');
    $dates_count = count($report);
    if(count($report) > 0) {
      $header = array(
      Resources::Get('report.byoperator.1'),
      Resources::Get('report.byoperator.2'),
      Resources::Get('report.byoperator.3'),
      Resources::Get('report.byoperator.6'),
      Resources::Get('report.byoperator.7'),
      Resources::Get('report.byoperator.11'),
      );

      $field_count = count($header);
      $length_chars = $field_count * self::FIELD_LENGTH + $field_count;
      $result .= self::makeBorder($field_count, self::FIELD_LENGTH) . "\n";
      $result .= self::formatTableLine($header, self::FIELD_LENGTH) . "\n";
      $result .= self::makeBorder($field_count, self::FIELD_LENGTH) . "\n";

      $middle = floor($length_chars/2);

      foreach($report as $d => $r) {
        if($dates_count > 1) {
          $result .= sprintf("%".($middle+strlen($d))."s", $d) . "\n";
          $result .= self::makeBorder($field_count, self::FIELD_LENGTH) . "\n";
        }

        foreach ($r as $v) {
          $v = array(
          $v['name'],
          $v['threads'],
          isset($v['messages']) ? $v['messages'] : '',
          $v['online_time'],
          $v['online_chatting_time'],
          $v['invited_users']
          );

          $result .= self::formatTableLine($v, self::FIELD_LENGTH) . "\n";
          $result .= self::makeBorder($field_count, self::FIELD_LENGTH) . "\n";
        }
      }
    }

    $result .= "\n\n";

    if($dates_count > 1) {
      $report = Thread::getInstance()->GetReportByAgent($start, $end);
      $data = array();
      foreach ($report as $v) {
        $data[] = array(
        $v['name'],
        $v['threads'],
        $v['messages'],
        $v['online_time'],
        $v['online_chatting_time'],
        $v['invited_users']
        );
      }

      $result .= Resources::Get('report.byoperator.title') . "\n";
      $result .= self::formatTable($header, $data, self::FIELD_LENGTH) . "\n\n";
    }

    $result .= Resources::Get('report.lostvisitors.title') . "\n";
    $report = MapperFactory::getMapper("LostVisitor")->getReportByOperator($start, $end);
    $data = array();
    $header = array(
    Resources::Get('report.lostvisitors.1'),
    Resources::Get('report.lostvisitors.2'),
    Resources::Get('report.lostvisitors.3')
    );

    if (!empty($report)) {
      foreach ($report as $v) {
        $data[] = array(
        $v['name'],
        isset($v['lost_vistors_count']) ? $v['lost_vistors_count']: 0 ,
        isset($v['avg_waittime_str']) ? $v['avg_waittime_str'] : 0
        );
      }
    }

    $result .= self::formatTable($header, $data, self::FIELD_LENGTH) . "\n\n";

    $result .= Resources::Get('report.interceptedvisitors.title') . "\n";
    $report = MapperFactory::getMapper("LostVisitor")->getReportInterceptedByOperator($start, $end);
    $data = array();
    $header = array(
    Resources::Get('report.interceptedvisitors.1'),
    Resources::Get('report.interceptedvisitors.2'),
    Resources::Get('report.interceptedvisitors.3')
    );

    if (!empty($report)) {
      foreach ($report as $v) {
        $data[] = array(
        $v['name'],
        isset($v['lost_vistors_count']) ? $v['lost_vistors_count'] : 0,
        isset($v['avg_waittime_str']) ? $v['avg_waittime_str'] : 0
        );
      }
    }

    $result .= self::formatTable($header, $data, self::FIELD_LENGTH);
    return $result;
  }

  public static function formatTable($header, $params, $field_length) {
    $field_count = count(reset($params));
    $result = "";

    $result .= self::makeBorder($field_count, $field_length) . "\n";

    if(!empty($header)) {
      $result .= self::formatTableLine($header, $field_length) . "\n";
    }

    foreach ($params as $p) {
      $result .= self::makeBorder($field_count, $field_length) . "\n";
      $result .= self::formatTableLine($p, $field_length) . "\n";
    }

    $result .= self::makeBorder($field_count, $field_length) . "\n";


    return $result;
  }

  public static function formatTableLine($values, $field_length) {
    $strings = array();

    foreach ($values as $v) {
      if(is_int($v)) {
        $v = sprintf("%d", $v);
      } else if (is_float($v)) {
        $v = sprintf("%.2f", $v);
      } else {
        $v = strval($v);
      }

      //TODO: not good block
      if(WEBIM_ENCODING == "UTF-8") {
        $v = smarticonv("UTF-8", "cp1251", $v);
      }

      $v = substr(sprintf("%{
      $field_length
      }s", $v), 0, $field_length);

      if(WEBIM_ENCODING == "UTF-8") {
        $v = smarticonv("cp1251", "UTF-8", $v);
      }

      $strings[] = $v;
    }

    return "|" . implode("|", $strings) . "|";
  }

  public static function makeBorder($field_count, $field_length) {
    $result = "|";
    $length = ($field_count * $field_length + $field_count - 1);
    for($i=0; $i<$length; $i++) {
      $result .= "-";
    }
    $result .= "|";

    return $result;
  }
}


?>
