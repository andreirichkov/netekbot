<?php

  class netekbot {

    private $log;

    public function __construct($log) {
      $this->log = $log;
    }

    public function processMessage($message) {
      $this->log->info('processing the message');

      // Declerations and Initializations
      $field;
      $fieldName;
      $backend = new backend($this->log);
      $db = new database($this->log);
      $usersMessage = $message->getMessage();
      $sameMessage = false;
      $uid = $message->getUser()->getUserId();
      $mail_message;
      $email_message_html;

      // Get the current phase for the current user
      $phase = $db->getPhase($uid);
      $this->log->info('the current phase for uid '.$uid.' is '.$phase);

      switch ($phase) {

        // SERVICE PROVIDER VALIDITY
        case 0:
          $this->log->info('entered phase 0');

          $serviceProvider = $backend->matchProvider($message->getMessage());

          $this->log->info('matching service provider');

          // If no match found we will get back 'not_found'
          if ($serviceProvider === 'not_found') {
            $message->setMessage('אני לא מכיר את הספק '.$usersMessage.' וודא שהקלדת את השם תקין');

            $this->log->info('the requested service provider was not found');
            break;

          } else {
            $this->log->info('requested service provider was found');

            $db->setServiceProvider($uid, $serviceProvider);
            $db->setPhase($uid, 1);

            $message->setMessage('בחרת להתנתק מ'.$db->getServiceProvider($uid).'.'.chr(10)
              .'על מנת לעזור לך להתנתק אני צריך מספר פרטים'.'.'.chr(10).chr(10)
              .'לפני שמתחילים חשוב לי לציין שהפרטיות שלך חשובה לי ולכן אני מתחייב לא לשמור ולא לשתף אף פרט שלך עם אף גורם צד ג');

            $sameMessage = true;
            // NOTICE! No break here for the fall-through behavior.
          }

        // Loop throgh all the needed fields
        case 1:
          $this->log->info('entered phase 1');

          // Check if its the same message set bofore. If it is add two new lines and instructions
          if ($sameMessage) {
            $message->setMessage($message->getMessage().chr(10).chr(10)
              .'כעת אשאל אותך מספר פרטים הכרחיים על מנת לנסח את בקשת ההתנתקות שלך'.'.'.chr(10).chr(10));
          }

          // Check if the current field is empty or not done
          $field = $db->getCurrentField($uid);
          // If the field is empty set it to the first needed field
          if ($field === 'empty') {
            $this->log->info('the field is empty');

            if ($sameMessage) {
              $message->setMessage($message->getMessage().$backend->getQuestionByFieldName($backend->getNextField($field)));
            } else {
              $message->setMessage($backend->getQuestionByFieldName($backend->getNextField($field)));
            }
            $db->setCurrentField($uid, $backend->getNextField($field));
            $db->setColumnValue($uid, 'first_name', $usersMessage);
            break;

          // Check if the last field was not reached
          } else if ($field !== 'done') {
            $this->log->info('the field is '.$field);

            // If its the one before the last one, set phase to 2
            if ($field === 'last_digits') {
              $db->setCurrentField($uid, $backend->getNextField($field));
              $db->setColumnValue($uid, $field, $usersMessage);
              $db->setPhase($uid, 2);
            } else {
              $message->setMessage($backend->getQuestionByFieldName($backend->getNextField($field)));
              $db->setCurrentField($uid, $backend->getNextField($field));
              $db->setColumnValue($uid, $field, $usersMessage);
              break;
            }

          } else if ($field === 'done') {

            $this->log->info('the field is done');
            $db->setPhase($uid, 2);
          }

          // NOTICE! No break here for the fall-through behavior.

        // Confirmation
        case 2:
          $this->log->info('entered phase 2');

          $message->setMessage('לפני שאשלח את ההודעה עליך לאמת שכל הפרטים נכונים'.':'.chr(10));

          $message->setMessage($message->getMessage().'ספק לניתוק'.': '.$db->getServiceProvider($uid).chr(10));

          $allFields = array('first_name', 'last_name', 'id_number', 'email_address', 'phone_number',
              'settlement', 'address', 'last_digits');
          $this->log->info('stepping into the for loop');
          for ($i = 0; $i < sizeof($allFields); $i++) {
            $message->setMessage($message->getMessage().$backend->getFieldHebrewTranslation($allFields[$i]).$db->getColumnValue($uid, $allFields[$i]).chr(10));
          }
          $this->log->info('out of the for loop');

          $message->setMessage(chr(10).$message->getMessage().'האם הם אכן נכונים'.'?'.' (כן/לא)');
          $db->setPhase($uid, 3);
          break;

        // Conclusion
        case 3:
          $this->log->info('entered phase 3');

          if ($message->getMessage() === 'לא') {
            $message->setMessage('אם כך בוא נתחיל מההתחלה. מה הספק שתרצה להתנתק ממנו'.'?');
            $db->setPhase($uid, 0);
            $db->deleteUIDFromDB($uid);
          } else if ($message->getMessage() === 'כן') {
            // GENERATE TAMPLATE AND MAIL IT
            $email_message = $backend->generatedTemplate($backend->matchProvider($db->getServiceProvider($uid)), $db->getColumnValue($uid, "phone_number")
            , $db->getColumnValue($uid, "first_name").' '.$db->getColumnValue($uid, "last_name"), $db->getColumnValue($uid, "id_number")
            , $db->getColumnValue($uid, "settlement"), $db->getColumnValue($uid, "address"), $db->getColumnValue($uid, "last_digits"), false);

            $email_message_html = $email_message = $backend->generatedTemplate($backend->matchProvider($db->getServiceProvider($uid)), $db->getColumnValue($uid, "phone_number")
            , $db->getColumnValue($uid, "first_name").' '.$db->getColumnValue($uid, "last_name"), $db->getColumnValue($uid, "id_number")
            , $db->getColumnValue($uid, "settlement"), $db->getColumnValue($uid, "address"), $db->getColumnValue($uid, "last_digits"), true);

            $backend->sendMail("huldoser@gmail.com", $db->getColumnValue($uid, "email_address"), $email_message, $email_message_html);

            $message->setMessage('הודעת דואר אלקטרונית הכוללת את המכתב לספק נשלחה אליך ול'.$db->getColumnValue($uid, "service_provider").' '.'והיא תגיע בדקות הקרובות'.'.'
              .chr(10).chr(10).'שמחתי לעזור לך ואשמח לעמוד לרשותך גם בעתיד'.'.'.chr(10).chr(10).'במידה ותרצה להתנתק מעוד ספק אנא רשום את שם הספק, אחרת אתה יכול לסגור את ההודעה'.'.');
            $db->deleteUIDFromDB($uid);
          } else {
            $message->setMessage('לא הבנתי למה אתה מתכוון. הם כן נכונים או לא'.'?');
          }
      }

      $this->log->info('returning message '.$message->getMessage());
      return $message;
    }

  }

?>
