<?php

namespace classes\Slack;


class SlackWebhookSender {

    public $error='';

    protected $slackHookUrl;

    public function __construct($slackHookUrl)
    {
        $this->slackHookUrl = $slackHookUrl;
    }

    /** Sends direct message to slack user
     * @param $user
     * @param $from
     * @param $text
     * @param bool $parameters
     * @return bool
     */
    public function directMessage($user, $from, $text, $parameters=false)
    {
        $slack_request='{
		    "channel": "@'.$user.'",
		    "username": "'.$from.'",
		    "text": "'.$text.'",
		    '.$parameters.'
        }';
        $answer=$this->sendRequest($slack_request);
        switch($answer)
        {
            case 'ok':
                $this->error='';
                return true;

            case false:
                $this->error='Cannot init curl session!';
                return false;

            default:
                $this->error=$answer;
                return false;

        }
    }

    /** Sends message to channel
     * @param $channel
     * @param $from
     * @param $text
     * @param bool $parameters
     * @return bool
     */
    public function sendToChannel($channel, $from,  $text, $parameters=false)
    {
        if($channel) $channel_string='"channel": "#'.$channel.'",'; else $channel_string='';
        $slack_request='{
		    '.$channel_string.'
		    "username": "'.$from.'",
		    "text": "'.$text.'",
		    '.$parameters.'
        }';
        $answer=$this->sendRequest($slack_request);
        switch($answer)
        {
            case 'ok':
                $this->error='';
                return true;

            case false:
                $this->error='Cannot init curl session!';
                return false;

            default:
                $this->error=$answer;
                return false;

        }
    }

    /** Sends request to Slack
     * @param $slack_request
     * @return bool|mixed
     */
    protected function sendRequest($slack_request)
    {
        if( $curl = curl_init() ) {
            curl_setopt($curl, CURLOPT_URL, $this->slackHookUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, 'payload='.$slack_request);
            $out = curl_exec($curl);
            curl_close($curl);

            return $out;
        } else return false;
    }

} 