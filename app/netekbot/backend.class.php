<?php

  class backend {
    private $log;

    public function __construct($log) {
      $this->log = $log;
    }


    public function matchProvider($serviceProvider) {
      switch ($serviceProvider) {
        case 'פלאפון':
        case 'pelephone':
        case 'פלא-פון':
          $serviceProvider = 'פלאפון';
          break;

        case 'סלקום':
        case ' cellcom':
          $serviceProvider = 'סלקום';
          break;

        case 'פרטנר':
        case 'partner':
        case 'אורנג':
          $serviceProvider = 'פרטנר';
          break;

        case 'רמי לוי':
        case 'rami levi':
          $serviceProvider = 'רמי לוי';
          break;

        case 'גולן טלאקום':
        case 'גולן טלקום':
        case 'גולן':
          $serviceProvider = 'גולן טלקום';
          break;

        case 'hot mobile':
        case 'הוט מובייל':
          $serviceProvider = 'הוט מובייל';
          break;

        case '012'.' '.'מובייל':
        case '012':
          $serviceProvider = '012 מובייל';

        default:
          $serviceProvider = 'not_found';
      }

      return $serviceProvider;
    }


    public function getQuestionByFieldName($fieldName) {
      switch ($fieldName) {
        case 'first_name':
          $fieldName = 'מה שמך הפרטי';
          break;

        case 'last_name':
          $fieldName = 'מה שם המשפחה שלך';
          break;

        case 'id_number':
          $fieldName = 'מה מספר תעודת הזהות שלך';
          break;

        case 'email_address':
          $fieldName = 'כדי שתקבל העתק של הבקשה שלך אני צריך גם את כתובת המייל שלך';
          break;

        case 'phone_number':
          $fieldName = 'איזה מספר תרצה לנתק';
          break;

        case 'settlement':
          $fieldName = 'באיזה ישוב אתה גר';
          break;

        case 'address':
          $fieldName = 'מה שם הרחוב, מספר הבית והדירה שלך';
          break;

        case 'last_digits':
          $fieldName = 'מה הם ארבעת הספרות האחרונות של אמצעי התשלום שבאמצעותו אתה משלם לספק';
          break;
      }

      return $fieldName.'?';
    }


    public function getFieldHebrewTranslation($fieldName) {
      switch ($fieldName) {
        case 'first_name':
          $fieldName = 'שם פרטי';
          break;

        case 'last_name':
          $fieldName = 'שם משפחה';
          break;

        case 'id_number':
          $fieldName = 'מספר זהות';
          break;

        case 'email_address':
          $fieldName = 'כתובת דואר אלקטרוני';
          break;

        case 'phone_number':
          $fieldName = 'מספר הטלפון לניתוק';
          break;

        case 'settlement':
          $fieldName = 'ישוב מגורים';
          break;

        case 'address':
          $fieldName = 'כתובת מגורים';
          break;

        case 'last_digits':
          $fieldName = 'ארבעה ספרות אחרונות';
          break;
      }

      return $fieldName.': ';
    }


    public function getNextField($currentFieldName) {
      $fields = array('first_name', 'last_name', 'id_number', 'email_address', 'phone_number',
        'settlement', 'address', 'last_digits', 'done');

      if ($currentFiledName === 'last_digits') {
        return 'done';
      } else if ($currentFieldName === 'empty') {
        return $fields[0];
      } else {
        for ($i = 0; $i < sizeof($fields); $i++) {
          if ($currentFieldName === $fields[$i]) {
            return $fields[$i + 1];
          }
        }
      }
    }


    public function generatedTemplate($serviceProvider, $phone_number, $full_name,$id_number, $settlement, $address,
      $last_digits, $html) {

        $template;

        if ($html) {
          $template ='לכבוד'.' '.$serviceProvider.'<br /><br />'
            .'בהתאם לסעיף 13ד לחוק הגנת הצרכן, '.'הריני מודיע על ביטול העסקה המתמשכת לאספקת שרותי תקשורת למנוי'.' '.$phone_number.'<br /><br />'
            .'שמי הוא '.$full_name.', '.'מספר תעודת הזהות שלי הינו'.' '.$id_number.', '.'כתובתי היא '.$address.', '.$settlement.' וארבעת הספרות האחרונות של האמצעי המשמש לתשלום הינו '.$last_digits.'.'.'<br /><br />'
            .'בהתאם לסעיף 13ד(ג) לחוק'.', '.'הנכם נדרשים לנתק אותי מיידית'.', '.'ולא מאוחר משלושה ימי עסקים מיום משלוח הודעה זו.'.'<br /><br />'
            .'באם לא תעשו כן'.', '.'על פי סעיף 13ד(ד) לחוק'.', '.'בית המשפט יהיה רשאי להטיל עליכם פיצויים עונשיים.'.'<br /><br />'
            .'הודעתי היא סופית'.', '.'ואני מבקש/ת כי לא יפנו אלי נציגי שירות לקוחות וכיוצא בזה.'.'<br /><br />'
            .'בברכה,'.'<br />'.$full_name;
        } else {
        $template = 'לכבוד'.' '.$serviceProvider.chr(10).chr(10)
          .'בהתאם לסעיף 13ד לחוק הגנת הצרכן, '.'הריני מודיע על ביטול העסקה המתמשכת לאספקת שרותי תקשורת למנוי'.' '.$phone_number.chr(10).chr(10)
          .'שמי הוא '.$full_name.', '.'מספר תעודת הזהות שלי הינו'.' '.$id_number.', '.'כתובתי היא '.$address.', '.$settlement.' וארבעת הספרות האחרונות של האמצעי המשמש לתשלום הינו '.$last_digits.'.'.chr(10).chr(10)
          .'בהתאם לסעיף 13ד(ג) לחוק'.', '.'הנכם נדרשים לנתק אותי מיידית'.', '.'ולא מאוחר משלושה ימי עסקים מיום משלוח הודעה זו.'.chr(10).chr(10)
          .'באם לא תעשו כן'.', '.'על פי סעיף 13ד(ד) לחוק'.', '.'בית המשפט יהיה רשאי להטיל עליכם פיצויים עונשיים.'.chr(10).chr(10)
          .'הודעתי היא סופית'.', '.'ואני מבקש/ת כי לא יפנו אלי נציגי שירות לקוחות וכיוצא בזה.'.chr(10).chr(10)
          .'בברכה,'.chr(10).$full_name;
      }
      return $template;
    }


    public function sendMail($to, $from, $message, $html_message) {
      $sendgrid = new SendGrid(config::getSendGrid('sgUserName'), config::getSendGrid('sgPassword'));

      $email = new SendGrid\Email(); // the backslash mean the function will be called from the global namespace
      $email->addTo([$to, $from])
        ->setFrom("DoNotReply@netekbot.co.il")
        ->setSubject('נתקבוט - בקשת ניתוק מספק שירות')
        ->setText($message)
        ->setHtml($html_message);

        $sendgrid->send($email);
    }


  }

?>
