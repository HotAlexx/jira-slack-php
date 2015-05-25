<?php

namespace classes\Slack;


class SlackWebhookSender {

    protected $slackHookUrl;

    public function __construct($slackHookUrl)
    {
        $this->slackHookUrl = $slackHookUrl;
    }

    public function directMessage($user, $from, $text, $parameters=false)
    {
        $slack_request='{
		    "channel": "@'.$user.'",
		    "username": "'.$from.'",
		    "text": "'.$text.'",
		    '.$parameters.'
        }';
        if($this->sendRequest($slack_request))
        {
            return true;

        } else return false;
    }

    public function sendToChannel($channel, $from,  $text, $parameters=false)
    {
        if($channel) $channel_string='"channel": "#'.$channel.'",'; else $channel_string='';
        $slack_request='{
		    '.$channel_string.'
		    "username": "'.$from.'",
		    "text": "'.$text.'",
		    '.$parameters.'
        }';
        if($this->sendRequest($slack_request))
        {
            return true;

        } else return false;
    }

    protected function sendRequest($slack_request)
    {
        if( $curl = curl_init() ) {
            curl_setopt($curl, CURLOPT_URL, $this->slackHookUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, 'payload='.$slack_request);
            $out = curl_exec($curl);
            curl_close($curl);

            return $out;
        } else return false;
    }

} 