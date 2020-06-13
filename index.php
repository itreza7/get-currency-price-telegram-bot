<?php

define('PATH', __DIR__ . '/');
define('UPLOAD_URL', 'https://rd7.ir/bot/upload/');
date_default_timezone_set('Asia/Tehran');

// Start of Loading DigitalCurrency Class and store a object
require_once PATH . 'DigitalCurrency.php';
$dataCurrency = new Larateam\DigitalCurrency;
// End of Loading DigitalCurrency Class and store a object

// Start of Loading Telegram Class and store a object
require_once PATH . 'TelegramErrorLogger.php';
require_once PATH . 'Telegram.php';
$telegram = new Larateam\Telegram('1047203139:AAH7M9aBkzWmVOJ8Avhlc5Nw3nDdkRCLP98');
// End of Loading Telegram Class and store a object

// Start of Loading Database PDO Class and store a object
$database = new PDO("mysql:host=localhost;dbname=cp39899_bot2", "cp39899_bot2", "B0D2I{S~.ZkZ", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
// End of Loading Database PDO Class and store a object

$siteName       = 'لارا تیم';
$admin_user_id  = 1006555624;
$result         = $telegram->getData();
$callback_data  = $telegram->callback_data();
$callback_query = $telegram->Callback_Query();
$photo          = isset($result['message']['photo'])                ? $result['message']['photo']               : null;
$document       = isset($result['message']['document'])             ? $result['message']['document']            : null;
$text           = isset($result['message']['text'])                 ? $result['message']['text']                : null;
$chat_id        = isset($result['message']['chat']['id'])           ? $result['message']['chat']['id']          : null;
$chat_type      = isset($result['message']['chat']['type'])         ? $result['message']['chat']['type']        : null;
$is_bot         = isset($result['message']['from']['is_bot'])       ? $result['message']['from']['is_bot']      : true;
$user_id        = isset($result['message']['from']['id'])           ? $result['message']['from']['id']          : null;
$first_name     = isset($result['message']['from']['first_name'])   ? $result['message']['from']['first_name']  : null;
$last_name      = isset($result['message']['from']['last_name'])    ? $result['message']['from']['last_name']   : null;
$username       = isset($result['message']['from']['username'])     ? $result['message']['from']['username']    : null;

function getCurrencyText($text)
{
    global $dataCurrency;
    $price_by_dollar = $dataCurrency->GetCurrencyPrice($text);
    $price_sell = $dataCurrency->GetSellPrice($text) / 10;
    $price_buy = $dataCurrency->GetBuyPrice($text) / 10;

    $sendText = '🖌'; // Emoji
    $sendText .= 'ارز:';
    $sendText .= "<b>$text</b>\n";
    $sendText .= "💵"; // Emoji
    $sendText .= ($price_by_dollar >= 1) ? sprintf(' ارزش کنونی : $ %.2f', $price_by_dollar) : sprintf(' ارزش کنونی : $ %.8f', $price_by_dollar);
    $sendText .= "\n";
    $sendText .= "📤"; // Emoji
    $sendText .= sprintf(' نرخ فروش : %.2f تومان', $price_sell);
    $sendText .= "\n";
    $sendText .= "📥"; // Emoji
    $sendText .= sprintf(' نرخ خرید : %.2f تومان', $price_buy);
    return $sendText;
}

function SetLocationInDB($location)
{
    global $database, $user_id;
    $sql = sprintf("UPDATE `Users` SET location='%s' WHERE user_id=%d", $location, $user_id);
    $database->query($sql);
}

function InfoMember()
{
    global $database, $user_id, $first_name, $last_name, $username;
    $executeQuery = $database->query(sprintf("SELECT * FROM Users WHERE user_id=%d LIMIT 1", $user_id));
    $executeQuery->setFetchMode(PDO::FETCH_ASSOC);
    $singUpMember = $executeQuery->fetch();
    if (!$singUpMember) {
        $sql = sprintf("INSERT INTO `Users`(`user_id`, `first_name`, `last_name`, `telegram_id`,`location`, `authentication_status`, `register_date`) VALUES (%d,'%s','%s','%s','%s',%d,%d)", $user_id, $first_name, $last_name, $username, 'home', 0, time());
        $database->exec($sql);
        $singUpMember = ['location' => 'home'];
    }
    return $singUpMember;
}

if (!is_null($result) && !$is_bot && $chat_type === 'private') {
    $infoMember     = InfoMember();
    $authentication = $infoMember['authentication_status'];
    $location       = $infoMember['location'];

    // Before Changing Location
    if      ($location === 'currency') {
        if ($text === 'صفحه اصلی') {
            $location = 'home';
            SetLocationInDB($location);
        }
    }
    elseif  ($location === 'profile') {
        $executeQuery = $database->query(sprintf("SELECT * FROM Authentication WHERE user_id=%d LIMIT 1", $user_id));
        $executeQuery->setFetchMode(PDO::FETCH_ASSOC);
        $signedResult = $executeQuery->fetch();
        if ($text === 'صفحه اصلی') {
            $location = 'home';
            SetLocationInDB($location);
        }
        elseif($text === 'ویرایش اطلاعات'){
            if($authentication == '1'){
                $location = 'edit-profile';
                SetLocationInDB($location);
            }else{
                $sendText = '⚠️ اطلاعات شما هنوز توسط مسئول گروه تایید نشده است ، لطفا تا تایید شدن اطلاعات صبر کنید.';
                $content = ['chat_id' => $chat_id, 'text' => $sendText];
                $telegram->sendMessage($content);
            }
        }
        else {
            if ($text === 'شروع فرآیند احراز هویت' && !$signedResult) {
                $location = 'profile-getName';
                SetLocationInDB($location);
            } elseif ($text === 'ادامه فرآیند احراز هویت' && $signedResult) {
                if (!isset($signedResult['name'])) {
                    $location = 'profile-getName';
                    SetLocationInDB($location);
                } elseif (!isset($signedResult['birthdate'])) {
                    $location = 'profile-getBirthdate';
                    SetLocationInDB($location);
                } elseif (!isset($signedResult['PersonalID'])) {
                    $location = 'profile-getPersonalID';
                    SetLocationInDB($location);
                } elseif (!isset($signedResult['EvidenceImage'])) {
                    $location = 'profile-getEvidenceImage';
                    SetLocationInDB($location);
                } elseif (!isset($signedResult['CardNumber'])) {
                    $location = 'profile-getCardNumber';
                    SetLocationInDB($location);
                } elseif (!isset($signedResult['CardImage'])) {
                    $location = 'profile-getCardImage';
                    SetLocationInDB($location);
                } elseif (!isset($signedResult['MobileNumber'])) {
                    $location = 'profile-getMobileNumber';
                    SetLocationInDB($location);
                } elseif (!isset($signedResult['HomeNumber'])) {
                    $location = 'profile-getHomeNumber';
                    SetLocationInDB($location);
                }
            }
        }
    }
    elseif  ($location === 'profile-getName') {
        if ($text === 'بازگشت به پروفایل') {
            $location = 'profile';
            SetLocationInDB($location);
        } else {
            if(preg_match('/\d/', $text, $output_array)){
                $content = ['chat_id' => $chat_id, 'text' => "❌ نام وارد شده شامل عدد است ، این یک الگوی ناصحیح است."];
                $telegram->sendMessage($content);
            }else{
                $content = ['chat_id' => $chat_id, 'text' => '✅' . $text . ' عزیز ،' . 'نام شما ثبت شد.'];
                $telegram->sendMessage($content);
                $sql = sprintf("UPDATE `Authentication` SET `name`='%s' WHERE user_id=%d", $text, $user_id);
                $database->query($sql);
                $location = 'profile-getBirthdate';
                SetLocationInDB($location);
            }
        }
    }
    elseif  ($location === 'profile-getBirthdate') {
        if ($text === 'بازگشت به پروفایل') {
            $location = 'profile';
            SetLocationInDB($location);
        } else {
            if(preg_match('/\d{4}\/\d{2}\/\d{2}/', $text, $output_array)){
                $content = ['chat_id' => $chat_id, 'text' => "✅ تاریخ تولد شما " . "(" . $text . ")" . ' با موفقیت ثبت شد.'];
                $telegram->sendMessage($content);
                $sql = sprintf("UPDATE `Authentication` SET `birthdate`='%s' WHERE user_id=%d", $text, $user_id);
                $database->query($sql);
                $location = 'profile-getPersonalID';
                SetLocationInDB($location);
            }else{
                $content = ['chat_id' => $chat_id, 'text' => "❌ تاریخ تولد وارد شده توسط شما مطابق الگو نیست."];
                $telegram->sendMessage($content);
            }
        }
    }
    elseif  ($location === 'profile-getPersonalID') {
        if ($text === 'بازگشت به پروفایل') {
            $location = 'profile';
            SetLocationInDB($location);
        } else {
            if(preg_match('/\d{10}/', $text, $output_array)){
                $content = ['chat_id' => $chat_id, 'text' => "✅ کد ملی شما " . "(" . $text . ")" . ' با موفقیت ثبت شد.'];
                $telegram->sendMessage($content);
                $sql = sprintf("UPDATE `Authentication` SET `PersonalID`='%s' WHERE user_id=%d", $text, $user_id);
                $database->query($sql);
                $location = 'profile-getEvidenceImage';
                SetLocationInDB($location);
            }else{
                $content = ['chat_id' => $chat_id, 'text' => "❌ کد ملی / شماره پاسپورت وارد شده توسط شما مطابق الگو نیست درصورتی که از اعداد فارسی استفاده میکنید زبان صفحه کلید خود را تغییر دهید."];
                $telegram->sendMessage($content);
            }
        }
    }
    elseif  ($location === 'profile-getEvidenceImage') {
        if ($text === 'بازگشت به پروفایل') {
            $location = 'profile';
            SetLocationInDB($location);
        } else {
            if(!is_null($photo) || (!is_null($document) && $document['mime_type'] === 'image/jpeg') ){
                $content = ['chat_id' => $chat_id, 'text' => "✅ تصویر شما ثبت شد."];
                $telegram->sendMessage($content);
                $file_id = !is_null($photo) ? $photo[count($photo) - 1]['file_id'] : $document['file_id'];
                $file = $telegram->getFile($file_id);
                $telegram->downloadFile($file['result']['file_path'], './upload/'.$user_id.'_'.time().'.jpg');
                $sql = sprintf("UPDATE `Authentication` SET `EvidenceImage`='%s' WHERE user_id=%d", UPLOAD_URL.$user_id.'_'.time().'.jpg', $user_id);
                $database->query($sql);
                $location = 'profile-getCardNumber';
                SetLocationInDB($location);
            }else{
                $content = ['chat_id' => $chat_id, 'text' => "❌ فایل ارسالی شما مورد تایید نیست ،‌لطفا مدرک تاییدی خود را در قالب یک عکس یا فایل عکس ارسال نمایید."];
                $telegram->sendMessage($content);
            }
        }
    }
    elseif  ($location === 'profile-getCardNumber') {
        if ($text === 'بازگشت به پروفایل') {
            $location = 'profile';
            SetLocationInDB($location);
        } else {
            if(preg_match('/\d{16}/', $text, $output_array)){
                $content = ['chat_id' => $chat_id, 'text' => "✅ شماره کارت شما " . "(" . $text . ")" . ' با موفقیت ثبت شد.'];
                $telegram->sendMessage($content);
                $sql = sprintf("UPDATE `Authentication` SET `CardNumber`='%s' WHERE user_id=%d", $text, $user_id);
                $database->query($sql);
                $location = 'profile-getCardImage';
                SetLocationInDB($location);
            }else{
                $content = ['chat_id' => $chat_id, 'text' => "❌ شماره کارت وارد شده توسط شما مطابق الگو نیست درصورتی که از اعداد فارسی استفاده میکنید زبان صفحه کلید خود را تغییر دهید."];
                $telegram->sendMessage($content);
            }
        }
    }
    elseif  ($location === 'profile-getCardImage') {
        if ($text === 'بازگشت به پروفایل') {
            $location = 'profile';
            SetLocationInDB($location);
        } else {
            if(!is_null($photo) || (!is_null($document) && $document['mime_type'] === 'image/jpeg') ){
                $content = ['chat_id' => $chat_id, 'text' => "✅ تصویر شما ثبت شد."];
                $telegram->sendMessage($content);
                $file_id = !is_null($photo) ? $photo[count($photo) - 1]['file_id'] : $document['file_id'];
                $file = $telegram->getFile($file_id);
                $telegram->downloadFile($file['result']['file_path'], './upload/'.$user_id.'_'.time().'.jpg');
                $sql = sprintf("UPDATE `Authentication` SET `CardImage`='%s' WHERE user_id=%d", UPLOAD_URL.$user_id.'_'.time().'.jpg', $user_id);
                $database->query($sql);
                $location = 'profile-getMobileNumber';
                SetLocationInDB($location);
            }else{
                $content = ['chat_id' => $chat_id, 'text' => "❌ فایل ارسالی شما مورد تایید نیست ،‌لطفا مدرک تاییدی خود را در قالب یک عکس یا فایل عکس ارسال نمایید."];
                $telegram->sendMessage($content);
            }
        }
    }
    elseif  ($location === 'profile-getMobileNumber') {
        if ($text === 'بازگشت به پروفایل') {
            $location = 'profile';
            SetLocationInDB($location);
        } else {
            $content = ['chat_id' => $chat_id, 'text' => "✅ شماره موبایل شما " . "(" . $text . ")" . ' با موفقیت ثبت شد.'];
            $telegram->sendMessage($content);
            $sql = sprintf("UPDATE `Authentication` SET `MobileNumber`='%s' WHERE user_id=%d", $text, $user_id);
            $database->query($sql);
            $location = 'profile-getHomeNumber';
            SetLocationInDB($location);
        }
    }
    elseif  ($location === 'profile-getHomeNumber') {
        if ($text === 'بازگشت به پروفایل') {
            $location = 'profile';
            SetLocationInDB($location);
        } else {
            $content = ['chat_id' => $chat_id, 'text' => "✅ شماره تلفن منزل شما " . "(" . $text . ")" . ' با موفقیت ثبت شد.'];
            $telegram->sendMessage($content);
            $sql = sprintf("UPDATE `Authentication` SET `HomeNumber`='%s' WHERE user_id=%d", $text, $user_id);
            $database->query($sql);
            /*
             * Ending Sign up
             */
            $content = ['chat_id' => $chat_id, 'text' => "✅ مراحل احراز هویت شما به پایان رسید ، بزودی اطلاعات شما مورد ارزیابی قرار گرفته و توسط مسئول مربوطه تایید میشود."];
            $telegram->sendMessage($content);
            $location = 'profile';
            SetLocationInDB($location);
            /*
             * Sending Data to Manager
             */
            $executeQuery = $database->query(sprintf("SELECT * FROM Authentication WHERE user_id=%d LIMIT 1", $user_id));
            $executeQuery->setFetchMode(PDO::FETCH_ASSOC);
            $signedResult = $executeQuery->fetch();
            $sendText = 'یک کاربر با مشخصات زیر مراحل تایید هویت خود را گذرانده است:'."\n\n";
            $sendText .= '✅ نام : '."\n```".$signedResult['name']."```\n\n";
            $sendText .= '✅ تاریخ تولد : '."\n```".$signedResult['birthdate']."```\n\n";
            $sendText .= '✅ کد شناسایی ( کد ملی / پاسپورت ) : '."\n```".$signedResult['PersonalID']."```\n\n";
            $sendText .= '✅ شماره کارت : '."\n```".$signedResult['CardNumber']."```\n\n";
            $sendText .= '✅ شماره تلفن همراه : '."\n```".$signedResult['MobileNumber']."```\n\n";
            $sendText .= '✅ شماره تلفن منزل : '."\n```".$signedResult['HomeNumber']."```\n\n";
            $accept_data = 'acceptingUser'.$signedResult['user_id'];
            $reject_data = 'rejectingUser'.$signedResult['user_id'];
            $option = [
                [
                    $telegram->buildInlineKeyBoardButton("تصویر مدرک شناسایی", $url=$signedResult['EvidenceImage']),
                    $telegram->buildInlineKeyBoardButton("تصویر کارت", $url=$signedResult['CardImage'])
                ],
                [
                    $telegram->buildInlineKeyBoardButton("تایید اطلاعات", '',$accept_data),
                    $telegram->buildInlineKeyBoardButton("رد اطلاعات", '',$reject_data)
                ],
            ];
            $keyboard = $telegram->buildInlineKeyBoard($option);
            $content = ['chat_id' => $admin_user_id, 'reply_markup' => $keyboard, 'parse_mode'=> 'Markdown', 'text' => $sendText];
            $telegram->sendMessage($content);
        }
    }
    elseif  ($location === 'edit-profile') {
        if ($text === 'بازگشت به پروفایل') {
            $location = 'profile';
            SetLocationInDB($location);
        } else {
        }
    }
    else    {
        if ($text === 'قیمت / خرید و فروش ارز') {
            $location = 'currency';
            SetLocationInDB($location);
        } elseif ($text === 'پروفایل کاربری') {
            $location = 'profile';
            SetLocationInDB($location);
        }
    }

    // After Changing Location
    if      ($location === 'currency') {
        $dataCurrency->initialData();
        $currencyArray = $dataCurrency->GetCurrencyList();
        $option[0][0] = $telegram->buildKeyboardButton("صفحه اصلی");
        $i = 1;
        $j = 0;
        foreach ($currencyArray as $slug) {
            $option[$i][$j] = $telegram->buildKeyboardButton($slug);
            if ($j === 4) {
                $i++;
                $j = 0;
            } else {
                $j++;
            }
        }
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        if (in_array(strtoupper($text), $currencyArray)) {
            $sendText = getCurrencyText(strtoupper($text));
        } else {
            $sendText = 'یکی از ارز های زیر را انتخاب کنید و یا نام اختصاری ارز را تایپ کنید.';
        }
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'parse_mode' => "HTML", 'text' => $sendText];
        $telegram->sendMessage($content);
    }
    elseif  ($location === 'profile') {
        $executeQuery = $database->query(sprintf("SELECT * FROM Authentication WHERE user_id=%d LIMIT 1", $user_id));
        $executeQuery->setFetchMode(PDO::FETCH_ASSOC);
        $signedResult = $executeQuery->fetch();
        if (!$signedResult) {
            $option = [
                [$telegram->buildKeyboardButton("صفحه اصلی"), $telegram->buildKeyboardButton("شروع فرآیند احراز هویت")],
            ];
        }
        else {
            if ($authentication == 1 && isset($signedResult['name'],$signedResult['birthdate'],$signedResult['PersonalID'],$signedResult['EvidenceImage'],$signedResult['CardNumber'],$signedResult['CardImage'],$signedResult['MobileNumber'],$signedResult['HomeNumber']))
            {
                $option = [
                    [$telegram->buildKeyboardButton("صفحه اصلی"),$telegram->buildKeyboardButton("ویرایش اطلاعات")],
                ];
            }
            elseif ($authentication == 0 && isset($signedResult['name'],$signedResult['birthdate'],$signedResult['PersonalID'],$signedResult['EvidenceImage'],$signedResult['CardNumber'],$signedResult['CardImage'],$signedResult['MobileNumber'],$signedResult['HomeNumber']))
            {
                $option = [
                    [$telegram->buildKeyboardButton("صفحه اصلی")],
                ];
            }
            else {
                $option = [
                    [$telegram->buildKeyboardButton("صفحه اصلی"), $telegram->buildKeyboardButton("ادامه فرآیند احراز هویت")],
                ];
            }
        }
        if ($authentication == 1){
            $sendText = $signedResult['name'].' عزیز ، به پروفایل کاربری خود خوش آمدید.' . "\n";
            $sendText .= 'درصورتی که قصد تغییر اطلاعات کاربری خود را دارید از منوی ویرایش اطلاعات اقدام نمایید.';
        }
        else{
            $sendText = 'کاربر گرامی به بخش پروفایل کاربری ، خوش آمدید.' . "\n";
            $sendText .= 'در حال حاضر شما احراز هویت خود را تکمیل نکرده اید و یا اطلاعات شما هنوز توسط مسئول مجموعه تایید نشده است ، درصورتی که قصد انجام مبادلات با مجموعه ما را دارید با استفاده از دکمه زیر اطلاعات هویتی خود را ارسال کنید و درصورتی که اطلاعات هویتی خود را ارسال کرده اید منتظر تایید توسط مجموعه باشید.';
        }
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'text' => $sendText];
        $telegram->sendMessage($content);
    }
    elseif  ($location === 'profile-getName') {
        $executeQuery = $database->query(sprintf("SELECT * FROM Authentication WHERE user_id=%d LIMIT 1", $user_id));
        $executeQuery->setFetchMode(PDO::FETCH_ASSOC);
        $signedResult = $executeQuery->fetch();
        if (!$signedResult) {
            $sql = sprintf("INSERT INTO `Authentication`(`user_id`) VALUES (%d)", $user_id);
            $database->exec($sql);
            $executeQuery = $database->query(sprintf("SELECT * FROM Authentication WHERE user_id=%d LIMIT 1", $user_id));
            $executeQuery->setFetchMode(PDO::FETCH_ASSOC);
            $signedResult = $executeQuery->fetch();
        }
        $option = [
            [$telegram->buildKeyboardButton("بازگشت به پروفایل")],
        ];
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        $sendText = '1️⃣';
        $sendText .= 'در مرحله اول نام و نام خانوادگی خود را وارد کنید.';
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'text' => $sendText];
        $telegram->sendMessage($content);
    }
    elseif  ($location === 'profile-getBirthdate') {
        $option = [
            [$telegram->buildKeyboardButton("بازگشت به پروفایل")],
        ];
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        $sendText = '2️⃣';
        $sendText .= 'در مرحله دوم تاریخ تولد خود را با اعداد انگلیسی و بصورت 1377/03/13 وارد کنید.';
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'text' => $sendText];
        $telegram->sendMessage($content);
    }
    elseif  ($location === 'profile-getPersonalID') {
        $option = [
            [$telegram->buildKeyboardButton("بازگشت به پروفایل")],
        ];
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        $sendText = '3️⃣' . 'لطفا کد ملی خود را همانند 1234567890 وارد کنید.' . "\n" . '⚠️ از وارد کردن کارکتر های دیگر، خودداری نمایید.' . "\n" . '⚠ درصورتی که اتباع ایرانی نیستید، شماره پاسپورت خود ( فقط ده رقم و بدون حرف اول ) را وارد کنید.';
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'text' => $sendText];
        $telegram->sendMessage($content);
    }
    elseif  ($location === 'profile-getEvidenceImage') {
        $option = [
            [$telegram->buildKeyboardButton("بازگشت به پروفایل")],
        ];
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        $sendText = '4️⃣'.'جهت تایید اطلاعات خود ، لطفا بصورت زیر عمل کنید:'."\n\n";
        $sendText .= '🔶'.'روی یک کاغذ متن زیر را بنویسید:'."\n";
        $sendText .= 'اینجانب رضا شیرازی با قصد خرید ارز دیجیتال از مجموعه '.$siteName.'  ، درخواست تایید مدارک خود را دارم.'."\n\n";
        $sendText .= '🔶'.'یکی از مدارک زیر را، کنار آن قرار دهید.'."\n";
        $sendText .= '(پاسپورت / شناسنامه / کارت ملی / کارت اقامت برای اتباع خارجی)(گواهینامه مورد قبول نمیباشد.)'."\n\n";
        $sendText .= '🔶'.'توجه داشته باشید که مدارک یا کاغذ ، بصورت کاملا جدا از هم باشند و روی یکدیگر قرار نگیرند.'."\n\n";
        $sendText .= '🔶'.'عکس واضح از موارد فوق تهیه کرده و ارسال فرمایید.'."\n\n";
        $sendText .= '‼️'.'میتوانید موارد غیرنیاز(مثلا شناسه الکترونیکی پاسپورت) را مخفی کنید.'."\n\n";
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'text' => $sendText];
        $telegram->sendMessage($content);
    }
    elseif  ($location === 'profile-getCardNumber') {
        $option = [
            [$telegram->buildKeyboardButton("بازگشت به پروفایل")],
        ];
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        $sendText = '5️⃣'.'شماره کارت خود را بصورت زیر و با استفاده از اعداد انگلیسی و بدون فاصله و خط تیره وارد نمایید:'."\n";
        $sendText .= '*6037997312345678*';
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'parse_mode' => "Markdown", 'text' => $sendText];
        $telegram->sendMessage($content);
    }
    elseif  ($location === 'profile-getCardImage') {
        $option = [
            [$telegram->buildKeyboardButton("بازگشت به پروفایل")],
        ];
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        $sendText = '6️⃣️⃣'.'لطفا جهت تایید کارت خود با در دست گرفت کارت و معلوم بودن حداقل موارد : شماره کارت ، تاریخ انقضا و نام صاحب حساب روی آن و قرار دادن آن در کنار مدرک شخصی کارت ملی / شناسنامه یک عکس تهیه کرده و ارسال فرمایید.'."\n";
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'text' => $sendText];
        $telegram->sendMessage($content);
    }
    elseif  ($location === 'profile-getMobileNumber') {
        $option = [
            [$telegram->buildKeyboardButton("بازگشت به پروفایل")],
        ];
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        $sendText = '7️⃣'.'شماره تلفن همراه خود را وارد کنید:';
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'parse_mode' => "Markdown", 'text' => $sendText];
        $telegram->sendMessage($content);
    }
    elseif  ($location === 'profile-getHomeNumber') {
        $option = [
            [$telegram->buildKeyboardButton("بازگشت به پروفایل")],
        ];
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        $sendText = '8️⃣'.'شماره تلفن منزل خود را وارد کنید.';
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'text' => $sendText];
        $telegram->sendMessage($content);
    }
    elseif  ($location === 'edit-profile'){
        $option = [
            [$telegram->buildKeyboardButton("بازگشت به پروفایل"),$telegram->buildKeyboardButton("تفییر اطلاعات کارت بانکی")],
            [$telegram->buildKeyboardButton("تفییر تلفن همراه"),$telegram->buildKeyboardButton("تغییر تلفن منزل")],
        ];
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        $sendText = 'بزودی..';
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'text' => $sendText];
        $telegram->sendMessage($content);
    }
    else    {
        $option = [
            [
                $telegram->buildKeyboardButton("قیمت / خرید و فروش ارز"), $telegram->buildKeyboardButton("پروفایل کاربری")
            ]
        ];
        $keyboard = $telegram->buildKeyBoard($option, false, true);
        $content = ['chat_id' => $chat_id, 'reply_markup' => $keyboard, 'text' => 'به ربات ' . $siteName . ' خوش آمدید. لطفا یکی از گزینه های زیر را انتخاب نمایید.'];
        $telegram->sendMessage($content);
    }
}

if(!is_null($callback_data) && $callback_query['message']['chat']['id'] == $admin_user_id){
    $infoMember     = InfoMember();
    if(strpos($callback_data,'acceptingUser') !== false || strpos($callback_data,'rejectingUser') !== false ){
        $array = explode('User', $callback_data);
        if($array[0] === 'rejecting'){
            $sql = sprintf("DELETE FROM `Authentication` WHERE user_id=%d", $array[1]);
            $database->query($sql);

            $sql = sprintf("UPDATE `Users` SET `authentication_status`=%d WHERE user_id=%d", 0, $array[1]);
            $database->query($sql);

            $content = ['chat_id' => $array[1], 'text' => '❌ احراز هویت شما رد شد.'];
            $telegram->sendMessage($content);

            $content = ['chat_id' => $admin_user_id, 'text' => '❌ احراز هویت کاربر رد شد.'];
            $telegram->sendMessage($content);

            $telegram->deleteMessage(['message_id'=>$callback_query['message']['message_id'],'chat_id'=>$callback_query['message']['chat']['id']]);
        }
        elseif($array[0] === 'accepting'){
            $sql = sprintf("UPDATE `Users` SET `authentication_status`=%d WHERE user_id=%d", 1, $array[1]);
            $database->query($sql);

            $content = ['chat_id' => $array[1], 'text' => '✅ احراز هویت شما تایید شد.'];
            $telegram->sendMessage($content);

            $content = ['chat_id' => $admin_user_id, 'text' => '✅ احراز هویت کاربر تایید شد.'];
            $telegram->sendMessage($content);

            $telegram->deleteMessage(['message_id'=>$callback_query['message']['message_id'],'chat_id'=>$callback_query['message']['chat']['id']]);
        }
    }
}