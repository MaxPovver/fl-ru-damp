<?php

if (PHP_SAPI != 'cli') {
    header('Location: /404.php');
}

if (!$_SERVER['DOCUMENT_ROOT']) {
    $_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../');
}
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/account.php');

class failed_gift extends account {
    
    /**
     * Начисление неначисленных подарков)
     * 
     */
    public function failedGifts() {
        $db = new DB('master');
        
        $sql = "SELECT * FROM __tmp_failed_gifts WHERE processed = 0 order by billing_id, op_id";
        $res = $db->rows($sql);
        $admin_uid = 103;
//        $admin_uid = 1;
        
        foreach ($res as $row) {
            $op_code = $row['op_code'];
            $payment_sys = $row['payment_sys'];
            $trs_sum = $row['trs_sum'];
            $gid = $row['uid'];
            $dep_id = $row['billing_id'];
            $is_emp = $row['is_emp'];

            // акция с Webmoney ---------------------
            if ($op_code == 12) {
                if ($payment_sys == 10 || $payment_sys == 2) { // WMR
                    if (!$is_emp) { // подарок фрилансеру
                        if ($trs_sum >= 2000 && $trs_sum < 5000) {
                            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/payed.php' );

                            $payed = new payed();
                            $bill_id = $gift_id = 0;
                            $tr_id = $this->start_transaction($admin_uid);

                            $payed->GiftOrderedTarif($bill_id, $gift_id, $gid, $admin_uid, $tr_id, '1', 'Аккаунт PRO в подарок при пополнении счета с помощью WebMoney', 91);
                        } elseif ($trs_sum >= 5000) {
                            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/firstpage.php' );

                            $bill_id = $gift_id = 0;
                            $tr_id = $this->start_transaction($admin_uid);
                            $payed = new firstpage();

                            $payed->GiftOrdered($bill_id, $gift_id, $gid, $admin_uid, $tr_id, '1 week', 93, 'Первая страница в подарок при пополнении счета с помощью WebMoney', 'Первая страница в подарок при пополнении счета с помощью WebMoney');
                        }
                    } else { // подарок работодателю
                        if ($trs_sum >= 1000 && $trs_sum < 5000) {
                            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/payed.php' );

                            $payed = new payed();
                            $bill_id = $gift_id = 0;
                            $tr_id = $this->start_transaction($admin_uid);

                            $payed->GiftOrderedTarif($bill_id, $gift_id, $gid, $admin_uid, $tr_id, '1', 'Аккаунт PRO в подарок при пополнении счета с помощью WebMoney', 92);
                        } elseif ($trs_sum >= 5000) {
                            $bill_id = $gift_id = 0;
                            $tr_id = $this->start_transaction($admin_uid);

                            $error = $this->Gift($bill_id, $gift_id, $tr_id, 93, $admin_uid, $gid, 'Платный проект в подарок при пополнении счета с помощью WebMoney', '');
                            if ($error === 0) {
                                // Добавляем подарочное бабло на бонусный счет.
                                $this->depositBonusEx($dep_id, 85, 'Начисление денег на платный проект в подарок', '', 40);
                            }
                        }
                    }
                } else {





                    // WMZ - пока нет
                }
            }

            //акция банк/сбер
            if ($op_code == 12 && ($payment_sys == 4 || $payment_sys == 5)) {

                $_opstr = $payment_sys == 5 ? "через квитанцию Сбербанка" : "через безналичный расчет";

                if (!$is_emp) { // подарок фрилансеру
                    if ($trs_sum >= 2000 && $trs_sum < 5000) {
                        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/payed.php' );

                        $_opcode = $payment_sys == 5 ? 95 : 99;

                        $payed = new payed();
                        $bill_id = $gift_id = 0;
                        $tr_id = $this->start_transaction($admin_uid);

                        $payed->GiftOrderedTarif($bill_id, $gift_id, $gid, $admin_uid, $tr_id, '1', "Аккаунт PRO в подарок при пополнении счета {$_opstr}", $_opcode);
                    } elseif ($trs_sum >= 5000) {
                        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/firstpage.php' );

                        $_opcode = $payment_sys == 5 ? 97 : 101;

                        $bill_id = $gift_id = 0;
                        $tr_id = $this->start_transaction($admin_uid);
                        $payed = new firstpage();

                        $payed->GiftOrdered($bill_id, $gift_id, $gid, $admin_uid, $tr_id, '1 week', $_opcode, "Первая страница в подарок при пополнении счета {$_opstr}", "Первая страница в подарок при пополнении счета {$_opstr}");
                    }
                } else {    // подарок работодателю
                    if ($trs_sum >= 1000 && $trs_sum < 5000) {
                        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/payed.php' );

                        $_opcode = $payment_sys == 5 ? 96 : 100;

                        $payed = new payed();
                        $bill_id = $gift_id = 0;
                        $tr_id = $this->start_transaction($admin_uid);

                        $payed->GiftOrderedTarif($bill_id, $gift_id, $gid, $admin_uid, $tr_id, '1', "Аккаунт PRO в подарок при пополнении счета {$_opstr}", $_opcode);
                    } elseif ($trs_sum >= 5000) {
                        $bill_id = $gift_id = 0;
                        $tr_id = $this->start_transaction($admin_uid);

                        $_opcode = $payment_sys == 5 ? 97 : 101;

                        $error = $this->Gift($bill_id, $gift_id, $tr_id, $_opcode, $admin_uid, $gid, "Платный проект в подарок при пополнении счета {$_opstr}", "Платный проект в подарок при пополнении счета {$_opstr}");
                        if ($error === 0) {
                            // Добавляем подарочное бабло на бонусный счет.
                            $this->depositBonusEx($dep_id, 85, 'Начисление денег на платный проект в подарок', '', 40);
                        }
                    }
                }
            }
            var_dump($row['op_id']);
            $db->update('__tmp_failed_gifts', array('processed' => 1), 'op_id = ?', $row['op_id']);
        }
        
    }
}

$acc = new failed_gift();
$acc->failedGifts();