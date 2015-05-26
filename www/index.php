<?

use classes\JiraSlackIntegration;

spl_autoload_register(function ($class) {
    require_once str_replace('\\', '/', $class). '.php';
});

$jiraSlackIntegration = new JiraSlackIntegration();
$jiraSlackIntegration->run();
