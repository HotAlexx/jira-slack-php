<?php
/**
 * Class for Jira&Slack integration
 */

namespace classes;

use classes\Jira\JiraHookReceiver;
use classes\Slack\SlackWebhookSender;


class JiraSlackIntegration {

    public $slackHookUrl='https://hooks.slack.com/services/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';
    public $jiraHookReceiver;

    /**
     * @var array Channels mapping
     */
    public $projectsToChannels=array(
        'CUR' => 'cursolog',
        'SMAR' => 'navigator',
        'SITES' => 'sites',
        'CMS' => 'cms',
        'FCRM' => 'fin_crm',
    );

    /**
     * @var array Users mapping
     */
    public $jiraUsersToSlack=array(
        'admin' => 'hotalex',
        'dimon9107' => 'dvm',
    );

    /**
     * @var string logfile. Please create this directory and set writing permissions!
     */
    protected $logfile="logs/errors.log";

    /**
     * Runs integration
     */
    public function run()
    {

        $this->jiraHookReceiver = new JiraHookReceiver();
        if($this->jiraHookReceiver->getData())
        {

            $slaskWebhookSender=new SlackWebhookSender($this->slackHookUrl);
            $channel=$this->getDestinationChannel();
            switch($this->jiraHookReceiver->data->webhookEvent)
            {
                case 'jira:issue_created':
                    if(!$slaskWebhookSender->sendToChannel($channel, 'jira-updates', '', $this->templateIssueCreated($this->jiraHookReceiver->data)))
                        $this->log($slaskWebhookSender->error);
                    break;
                case 'jira:issue_deleted':
                    if(!$slaskWebhookSender->sendToChannel($channel, 'jira-updates', '', $this->templateIssueDeleted($this->jiraHookReceiver->data)))
                        $this->log($slaskWebhookSender->error);
                    break;
                default:
                    if(!$slaskWebhookSender->sendToChannel($channel, 'jira-updates', '', $this->templateIssueUpdated($this->jiraHookReceiver->data)))
                        $this->log($slaskWebhookSender->error);

            }


        }
        else
        {
            $this->log($this->jiraHookReceiver->error);
        }

    }

    /** Gets channel by Jira Project key
     * @return bool False if channel doesn't exist
     */
    protected function getDestinationChannel()
    {
        $project_key=$this->jiraHookReceiver->data->issue->fields->project->key;
        if(isset($this->projectsToChannels[$project_key]))
            return $this->projectsToChannels[$project_key];
        else
            return false;
    }

    /** Logger
     * @param $message
     */
    protected function log($message)
    {
        $date=date('d.m.Y H:i:s');
        file_put_contents($this->logfile, "[".$date."] ".$message."\n", FILE_APPEND);
    }

    /*
    protected function getDump($var)
    {
        ob_start();
        var_dump($var);
        return ob_get_clean();
    }
    */

    /** Template for create issue event
     * @param $data Jira Hook data
     * @return string Data for sending to Slack
     */
    protected function templateIssueCreated($data)
    {
        $pretext="<https://plusonedev.atlassian.net/secure/ViewProfile.jspa?name=".$data->user->key."|".$data->user->displayName."> created a ".$data->issue->fields->issuetype->name." <https://plusonedev.atlassian.net/browse/".$data->issue->key."|".$data->issue->key.">";
        if($data->issue->fields->assignee)
            $assignee_field=',{
                                    "title": "Assignee",
                                    "value": "'.$data->issue->fields->assignee->displayName.'",
                                    "short": false
            }';
        else $assignee_field='';

        return '"attachments": [
                        {
                            "color": "#36a64f",
                            "pretext": "'.$pretext.'",

                            "fields": [
                                {
                                    "title": "Summary",
                                    "value": "'.$data->issue->fields->summary.'",
                                    "short": false
                                }
                                '.$assignee_field.'
                            ]
                        }
                ]';
    }

    /** Template for delete issue event
     * @param $data Jira Hook data
     * @return string Data for sending to Slack
     */
    protected function templateIssueDeleted($data)
    {
        $pretext="<https://plusonedev.atlassian.net/secure/ViewProfile.jspa?name=".$data->user->key."|".$data->user->displayName."> deleted a ".$data->issue->fields->issuetype->name." <https://plusonedev.atlassian.net/browse/".$data->issue->key."|".$data->issue->key.">";

        return '"attachments": [
                        {
                            "color": "#36a64f",
                            "pretext": "'.$pretext.'",
                            "fields": [
                                {
                                    "title": "Summary",
                                    "value": "'.$data->issue->fields->summary.'",
                                    "short": false
                                }
                            ]
                        }
                ]';
    }

    /** Template for update issue event
     * @param $data Jira Hook data
     * @return string Data for sending to Slack
     */
    protected function templateIssueUpdated($data)
    {
        $pretext=$data->issue->fields->issuetype->name." <https://plusonedev.atlassian.net/browse/".$data->issue->key."|".$data->issue->key."> is updated by <https://plusonedev.atlassian.net/secure/ViewProfile.jspa?name=".$data->user->key."|".$data->user->displayName.">";
        $changelog=$data->changelog->items;
        $fields='';
        foreach($changelog as $item)
        {
            if($fields != '') $fields.=', ';
            $fields.='{
                        "title": "'.$item->field.'",
                        "value": "'.$item->fromString.' -> '.$item->toString.'",
                        "short": false
                      }';
        }
        return '"attachments": [
                        {
                            "color": "#36a64f",
                            "pretext": "'.$pretext.'",

                            "fields": [
                                '.$fields.'
                            ]
                        }
                ]';
    }
} 