<?php


namespace classes\Jira;


class JiraHookReceiver {

    public $data;
    public $error;

    /** Gets data form Jira hook request
     * @return bool
     */
    public function getData()
    {
        $f = fopen('php://input', 'r');

        $data = stream_get_contents($f);

        if ($data)
        {

            $this->data=json_decode($data);
            return true;
        }
        else
        {
            $this->error='Data is not received!';
            return false;
        }
    }

} 