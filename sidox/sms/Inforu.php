<?php

namespace Sidox\SMS;

/**
 * @author Pol Bogopolsky <pol292@gmail.com>
 * @version 1.0 <03/04/2017>
 */
class Inforu {

    private $_inforu;
    private $_username;
    private $_password;
    private $_sender;

    /**
     * This it the constractor of Inforu class
     * @param string $username The username for Inforu System
     * @param string $password The password for Inforu System
     * @param string $sender The sender information
     * @param string $message The message for send
     * @param string|array $phoneNumbers The phone numbers
     */
    function __construct( $username, $password, $sender, $message = "", $phoneNumbers = "" ) {

        //securty:
        $this->_username = htmlentities( $username );
        $this->_password = htmlentities( $password );
        $this->_sender   = htmlentities( $sender );
        //create root element
        $this->_inforu   = new \SimpleXMLElement( '<InforuRoot/>' );
        $this->createMessage( $message, $phoneNumbers );
    }

    /**
     * This method return XML
     * @return string This method return XML string
     */
    private function getXMLasURL() {
        return urlencode( $this->_inforu->asXML() );
    }

    /**
     * This method get array of phone number and return string of this number
     * @param array $numbersArr The array of phone numbers
     * @return string The string of phone numbers
     */
    private function cutPhoneNumbers( &$numbersArr ) {
        return implode( ';', $numbersArr );
    }

    /**
     * This method add user connection and sender information to XML
     * @param SimpleXMLElement $node The node of message XML
     */
    private function createSetting( &$node ) {


        //Create sender Setting:
        $user = $node->addChild( 'User' );
        $user->addChild( 'Username', $this->_username );
        $user->addChild( 'Password', $this->_password );

        $settings = $node->addChild( 'Settings' );
        $settings->addChild( 'Sender', $this->_sender );
    }

    /**
     * This method create the message for send
     * @param string $message The message for send
     * @param string|array $phoneNumbers This is the list of phones number (if in string use Semicolon for multi number)
     */
    public function createMessage( $message, $phoneNumbers ) {
        if ( $phoneNumbers && $message ) { // check if message not empty and phone numbers
            //change Array of phone number To String
            if ( is_array( $phoneNumbers ) ) {
                $phoneNumbers = $this->cutPhoneNumbers( $phoneNumbers );
            }

            //securty:
            $phoneNumbers = htmlentities( $phoneNumbers );
            $message      = preg_replace( "/\r|\n/", "", $message ); // remove line breaks
            $message      = htmlentities( $message );

            //create new massage
            $inforuMessage = $this->_inforu->addChild( 'Inforu' );

            $this->createSetting( $inforuMessage );

            $content = $inforuMessage->addChild( 'Content' );
            $content->addAttribute( 'Type', 'sms' );
            $content->addChild( 'Message', $message );

            $recipients = $inforuMessage->addChild( 'Recipients' );
            $recipients->addChild( 'PhoneNumber', $phoneNumbers );
        }
        return $this;
    }

    /**
     * This method send sms
     * @param boolean $getXml how to get response <true for xml | false to table>
     * @return string The response of server
     */
    public function sendSMS( $getXml = TRUE ) {

        $ch       = curl_init();
        $url      = "http://api.inforu.co.il/SendMessageXml.ashx?InforuXML={$this->getXMLasURL()}";
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $response = curl_exec( $ch );
        curl_close( $ch );

        $this->_inforu = new \SimpleXMLElement( '<InforuRoot/>' );

        $response = "<Responses>{$response}</Responses>";
        return ($getXml) ? $response : $this->responseTable( $response );
    }

    /**
     * This method change XML to table
     * @param SimpleXMLElement $xml The responsed XML
     * @return string The table of all response
     */
    private function &responseTable( &$xml ) {
        $xml = new \SimpleXMLElement( $xml );
        $ret = '';
        $end = count( $xml->Result );
        for ( $i = 0; $i < $end; $i++ ) {
            $output = '<tr><th colspan="2">SMS ' . ($i + 1) . '</th></tr>';
            $output .= "<tr><td>Status</td><td>{$xml->Result[ $i ]->Status}</td></tr>";
            $output .= "<tr><td>Description</td><td>{$xml->Result[ $i ]->Description}</td></tr>";
            $output .= "<tr><td>NumberOfRecipients</td><td>{$xml->Result[ $i ]->NumberOfRecipients}</td></tr>";
            $ret    .= $output;
        }
        $ret = "<table class='sms-responses'>$ret</table>";
        return $ret;
    }

}
