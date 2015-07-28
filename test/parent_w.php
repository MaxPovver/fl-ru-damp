<? 
$reviewer = new StdClass();
$reviewer->uname = @$_POST["uname"];
$reviewer->usurname = @$_POST["usurname"];
$str = "Все рецензии от рецензента";
if ( strlen($reviewer->uname) > 0 && strlen($reviewer->usurname) > 0 ) {
            //Все рецензии от <Имя_Фамилия_в_родительском_падеже>
            $consonants = "бвгджзклмнпрстфхцчшщ";
            $vowels = "аеёиоуыэюя";
            $vLetter = $letter = $reviewer->uname[ strlen($reviewer->uname) - 1 ];
            $uname = $reviewer->uname;
            if ( $letter == "й" ) {
                $uname[ strlen($uname) - 1 ] = 'я';
            } elseif ( $letter == "ь" ) {
                $uname[ strlen($uname) - 1 ] = 'я';
            } elseif ( strpos($consonants, $letter ) !== false ) {
                $uname .= 'а';
            } elseif ( strpos($vowels, $letter ) !== false ) {
                if ( $letter == 'а' ) {
                    $prev = $reviewer->uname[ strlen($reviewer->uname) - 2 ];
                    if ( strpos($consonants, $prev) !== false) {
                        if ( $prev != 'ш' && $prev != 'ж' ) {
                            $uname[ strlen($uname) - 1 ] = 'ы';
                        } else {
                            $uname[ strlen($uname) - 1 ] = 'и';
                        }
                    } else {
                        $uname[ strlen($uname) - 1 ] = 'и';
                    }
                } else {
                    $uname[ strlen($uname) - 1 ] = 'и';
                }
            }
            
            $usurname = $reviewer->usurname;
            $letter = $reviewer->usurname[ strlen($reviewer->usurname) - 1 ];
            if ( $letter == "й" && $reviewer->usurname[ strlen($reviewer->usurname) - 2 ] == "и" ) {
                $usurname[ strlen($usurname) - 2 ] = 'о';
                $usurname[ strlen($usurname) - 1 ] = 'г';
                $usurname .= 'о';
            } elseif ( $letter == "я" && $reviewer->usurname[ strlen($reviewer->usurname) - 2 ] == "а" ) {
                $usurname[ strlen($usurname) - 2 ] = 'о';
                $usurname[ strlen($usurname) - 1 ] = 'й';
            } elseif ( strpos($consonants, $letter ) !== false && strpos($vowels, $vLetter ) === false ) {
                $usurname .= 'а';
            } elseif ( strpos($vowels, $letter ) !== false ) {
                if ( $letter != 'е' ) {$prev = $reviewer->uname[ strlen($reviewer->uname) - 2 ];
                    if ( $letter == 'а' ) {
                        $usurname[ strlen($usurname) - 1 ] = 'о';
                        $usurname .= 'й';
                    } elseif ( $letter == 'о' && strpos($vowels, $vLetter ) !== false ) {
                        ;
                    } else {
                        $usurname[ strlen($usurname) - 1 ] = 'и';
                    }
                }
            }
            
            $str = "Все рецензии от $uname $usurname";
}
?>
<form method="POST">
Uname: <input type="text" value="<?=$reviewer->uname?>" name="uname"/><br>
USurname: <input type="text" value="<?=$reviewer->usurname?>" name="usurname"/><br>
<input type="submit" value="Send" />
</form>
<div style="color:red"><?=$str?></div>