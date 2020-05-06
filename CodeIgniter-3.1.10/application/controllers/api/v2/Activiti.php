<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use Nette\Utils\Random;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Using Medoo namespace
use Medoo\Medoo;

use Activiti\Client\ModelFactory;
use Activiti\Client\ObjectSerializer;
use Activiti\Client\ServiceFactory;
use Activiti\Client\Model\User\UserQuery;
use Activiti\Client\Model\User\UserCreate;
use Activiti\Client\Model\User\UserUpdate;
use GuzzleHttp\Client;
use Activiti\Client\Model\Group\GroupQuery;



use Activiti\Client\Exception as ActivitiException; // 解决与 PHPMailer\PHPMailer\Exception 同名冲突

use Activiti\Client\Service\DeploymentService;
use Activiti\Client\Service\GroupService;
use Activiti\Client\Service\HistoryService;
use Activiti\Client\Service\ManagementService;
use Activiti\Client\Service\ProcessDefinitionService;
use Activiti\Client\Service\ProcessInstanceService;
use Activiti\Client\Service\TaskService;
use Activiti\Client\Service\UserService;


use Activiti\Client\Model\Deployment\Deployment;
use Activiti\Client\Model\Deployment\DeploymentList;
use Activiti\Client\Model\Deployment\DeploymentQuery;

use Activiti\Client\Model\ProcessDefinition\ProcessDefinition;
use Activiti\Client\Model\ProcessDefinition\ProcessDefinitionList;
use Activiti\Client\Model\ProcessDefinition\ProcessDefinitionQuery;

use Activiti\Client\Model\ProcessInstance\ProcessInstanceCreate;
use Activiti\Client\Model\ProcessInstance\ProcessInstanceQuery;
use Activiti\Client\Model\Task\TaskQuery;

use Activiti\Client\Model\VariableCreate;
use Activiti\Client\Model\VariableUpdate;
use Activiti\Client\Model\VariableQuery;
use Activiti\Client\Model\Variable;
use Activiti\Client\Model\VariableList;

class Activiti extends RestController
{
    private $Medoodb;

    function __construct()
    {
        parent::__construct();
        // Initialize
        $this->Medoodb = new Medoo(config_item('medoodb'));
    }

    public function activiti_post()
    {
        $client = new Client([
            'base_uri' => 'http://localhost:8080/activiti-rest/service/',
            'auth' => [
                'lily', 'lily', // start process initiator 变量设置，这样需要驳回调整申请的时候可以使用此变量作为$assignee
            ],
        ]);

        // // 用户 list
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // $service = $serviceFactory->createUserService();

        // $query = new UserQuery();
        // $query->setSize(10); // 设置分页 setSize setStart

        // do {
        //     $users = $service->getUsersList($query);

        //     foreach ($users as $i => $user) {
        //         vprintf("%d. %s %s (%s) <%s>\n", [
        //             $query->getStart() + $i + 1,
        //             $user->getFirstName(),
        //             $user->getLastName(),
        //             $user->getId(),
        //             $user->getEmail(),
        //         ]);
        //     }

        //     $query->setStart($query->getStart() + $query->getSize());
        // } while ($users->getTotal() > $query->getStart());

        // // 组 list
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // $service = $serviceFactory->createGroupService();

        // $query = new GroupQuery();
        // $query->setSize(5); // 分页 setSize setStart

        // do {
        //     $groups = $service->getGroupList($query);
        //     foreach ($groups as $i => $group) {
        //         printf("%s (%s)\n", $group->getName(), $group->getType());
        //     }

        //     $query->setStart($query->getStart() + $query->getSize());
        //     var_dump($query->getStart());
        // } while ($groups->getTotal() > $query->getStart());

        // // 创建group
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // try {
        //     $group = $serviceFactory->createGroupService()->createGroup('Group A', 'Group a', 'group-a'); //  ID_, Name_, TYPE_
        // } catch (ActivitiException\ActivitiException $e) {
        //     var_dump($e->getMessage());
        // }

        // // 删除group
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // try {
        //     $group = $serviceFactory->createGroupService()->deleteGroup('Group A'); //  ID_, Name_, TYPE_
        //     var_dump('删除group success!');
        // } catch (ActivitiException\ActivitiException $e) {
        //     var_dump($e->getMessage());
        // }

        // // 更新group
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // try {
        //     $group = $serviceFactory->createGroupService()->updateGroup('Group A', 'new GroupName', 'New Type'); //  ID_, Name_, TYPE_
        //     var_dump('更新group success!');
        // } catch (ActivitiException\ActivitiException $e) {
        //     var_dump($e->getMessage());
        // }

        // // 创建 user
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // try {
        //     $data = new UserCreate();
        //     $data->setId('testuser');
        //     $data->setFirstName('testName');
        //     // $data->setLastName('McDonald');
        //     $data->setEmail('no-reply@activiti.org'); // 必须配置邮箱 否则 activiti-app 不能登录成功
        //     $data->setPassword('testuser');

        //     $user = $serviceFactory->createUserService()->createUser($data);
        //     var_dump('创建 用户成功');
        // } catch (ActivitiException\ActivitiException $e) {
        //     var_dump($e->getMessage());
        // }

        // // 删除 user
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // try {
        //     $user = $serviceFactory->createUserService()->deleteUser('testuser');
        //     var_dump('删除用户成功');
        // } catch (ActivitiException\ActivitiException $e) {
        //     var_dump($e->getMessage());
        // }

        // // 更新 user
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // try {
        //     $data = new UserUpdate();
        //     $data->setFirstName('testName');
        //     // $data->setLastName('McDonald');
        //     // $data->setEmail('no-reply@activiti.org');
        //     $data->setPassword('testuser');
        //     $group = $serviceFactory->createUserService()->updateUser('testuser', $data);

        //     var_dump('更新group success!');
        // } catch (ActivitiException\ActivitiException $e) {
        //     var_dump($e->getMessage());
        // }

        // // 添加 group memmber addMember 
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // try {
        //     $group = $serviceFactory->createGroupService()->addMember('user','testuser');
        //     var_dump('addMember success');
        // } catch (ActivitiException\ActivitiException $e) {
        //     var_dump($e->getMessage());
        // }

        // // 删除 group memmber deleteMember => act_id_membership
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // try {
        //     $group = $serviceFactory->createGroupService()->deleteMember('user','testuser');
        //     var_dump('deleteMember success');
        // } catch (ActivitiException\ActivitiException $e) {
        //     var_dump($e->getMessage());
        // }


        // // getDeploymentList()
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // $deployment = $serviceFactory->createDeploymentService()->getDeploymentList();
        // var_dump($deployment->getTotal());
        // var_dump($deployment->getIterator()[5]->getId()); // 5189
        // var_dump($deployment->getIterator()[5]->getName());
        // var_dump($deployment->getIterator()[5]->getUrl());
        // // var_dump($deployment->getIterator()[5]);

        // // getDeployment($id)
        // $deployment = $serviceFactory->createDeploymentService()->getDeployment(5189);
        // var_dump($deployment->getId());
        // var_dump($deployment->getName());
        // var_dump($deployment->getUrl());


        // // getProcessDefinitionList
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // $processDefinitions = $serviceFactory->createProcessDefinitionService()->getProcessDefinitionList(new ProcessDefinitionQuery());

        // var_dump($processDefinitions->getTotal()); // class ProcessDefinitionList extends AbstractList
        // // var_dump($processDefinitions->getIterator());
        // var_dump($processDefinitions->getIterator()[7]->getId()); // leave_model_key:2:5078
        // var_dump($processDefinitions->getIterator()[7]->getName());
        // var_dump($processDefinitions->getIterator()[7]->getUrl());
        // // var_dump($processDefinitions->getIterator()[7]);
        // // string(22) "leave_model_key:2:5078"
        // // string(16) "请假Model_name"

        // // getProcessDefinition($id)
        // $processDefinitions = $serviceFactory->createProcessDefinitionService()->getProcessDefinition('leave_model_key:2:5078');
        // var_dump($processDefinitions->getId());
        // var_dump($processDefinitions->getName());
        // var_dump($processDefinitions->getUrl());

        // // getResourceData($id)
        // $processDefinitions = $serviceFactory->createProcessDefinitionService()->getResourceData('leave_model_key:2:5078');
        // var_dump($processDefinitions);



        // // getProcessInstanceList
        $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());

        // $query = new ProcessInstanceQuery();
        // $processInstanceList = $serviceFactory->createProcessInstanceService()->getProcessInstanceList($query);
        // var_dump($processInstanceList->getTotal());
        // // var_dump($processInstanceList->getIterator());
        // var_dump($processInstanceList->getIterator()[0]->getId()); // 5127
        // var_dump($processInstanceList->getIterator()[0]->getUrl());

        // // // getProcessInstance($id)
        // $processInstanceList = $serviceFactory->createProcessInstanceService()->getProcessInstance(2595);
        // var_dump($processInstanceList);
        // var_dump($processInstanceList->getId());
        // var_dump($processInstanceList->getUrl());

        // // getIdentityLinks
        // $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // $processInstanceList = $serviceFactory->createProcessInstanceService()->getIdentityLinks(2595);
        // // var_dump($processInstanceList->getIterator());
        // var_dump($processInstanceList->getIterator()[0]);
    }

    public function activitilily_post()
    {
        $client = new Client([
            'base_uri' => 'http://localhost:8080/activiti-rest/service/',
            'auth' => [
                'lily', 'lily', // 还是有关系的 start process 时 标注 starter 可看到
            ],
        ]);
        $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());
        // $processInstanceVariables = $serviceFactory->createProcessInstanceService()->getVariables(2888);
        // var_dump($processInstanceVariables);
        // return;
        $processInstanceId = 20034;
        $query = new TaskQuery();
        $query->setAssignee('lily'); // query todo task by assignee and processInstanceId
        $query->setProcessInstanceId($processInstanceId);
        $taskList = $serviceFactory->createTaskService()->queryTasks($query);
        var_dump($taskList); return; // task id 2934, processInstanceId 2927

        $taskId = 20049;

        // // 根据 taskid 创建当前任务中的变量。getVariables时会同时获得process instance中的变量
        // $taskVariables = [
        //     new VariableCreate('daystt', 'integer', 10),
        //     new VariableCreate('starttt', 'string', '2020-04-01'),
        //     new VariableCreate('endtt', 'string', '2020-04-05'),
        //     new VariableCreate('pass', 'boolean', false)
        //     // new VariableCreate('intProcVar', 'integer', 123)
        //     // new VariableCreate('intProcVar', 'string', 'hello')
        //     // boolean
        // ];
        // // $serviceFactory->createTaskService()->createVariables(2934, $taskVariables);

        $variables = $serviceFactory
            ->createTaskService($client)
            ->getVariables($taskId);
        foreach ($variables as $i => $variable) {
            printf("%s (%s) %s\n", $variable->getName(), $variable->getType(), $variable->getValue());
        }
        // delete pass 重置proccess变量 防止 驳回时 再次设置出错 不需要 complete(, $variable) 设置的变量可能重复设置

        try {
            // $serviceFactory->createTaskService()->complete($taskId, []); // json_decode error: Syntax error
            $serviceFactory->createTaskService()->complete($taskId); // json_decode error: Syntax error
            var_dump('lily complete success');
        } catch (ActivitiException\ActivitiException $e) {
            var_dump($e->getMessage());
        }
        return;

        // $taskList = $serviceFactory->createTaskService()->complete(2854,  $taskVariables);
        // // getTaskList
        // $taskList = $serviceFactory->createTaskService()->getTaskList();
        // var_dump($taskList->getTotal()); // 1
        // // var_dump($taskList->getIterator());
        // var_dump($taskList->getIterator()[0]->getId()); // 5207
        // var_dump($taskList->getIterator()[0]->getUrl());

        // getTask
        $taskList = $serviceFactory->createTaskService()->getTask(2854);
        // var_dump($taskList);   // ["formKey"]=> string(15) "员工请假单"
        var_dump($taskList->getId());
        var_dump($taskList->getUrl());
        var_dump($taskList->getName());
    }

    public function activitiboss_post()
    {
        $client = new Client([
            'base_uri' => 'http://localhost:8080/activiti-rest/service/',
            'auth' => [
                'boss', 'boss', // start process initiator 变量设置，这样需要驳回调整申请的时候可以使用此变量作为$assignee
            ],
        ]);

        $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());

        $processInstanceId = 20034;
        $query = new TaskQuery();
        $query->setAssignee('boss'); // query todo task by assignee and processInstanceId
        $query->setProcessInstanceId($processInstanceId);
        $taskList = $serviceFactory->createTaskService()->queryTasks($query);
        // var_dump($taskList); return;

        // 创建process设置的变量是全部流程

        $taskId = 20051;

        $variables = $serviceFactory
            ->createTaskService($client)
            ->getVariables($taskId);
        foreach ($variables as $i => $variable) {
            printf("%s (%s) %s\n", $variable->getName(), $variable->getType(), $variable->getValue());
        }
        // return;

        // 根据 taskid 创建当前任务中的变量。getVariables时会同时获得process instance中的变量

        $completeVar = [
            [
                'name' => 'pass',
                'type' => 'boolean',
                'value' => true
            ]
            // new VariableCreate('pass', 'boolean', 'false') // {"message":"Bad request","exception":"Variable name is required"}
        ];
        // var_dump($variables->getIterator());
        try {
            $serviceFactory->createTaskService()->complete($taskId, $completeVar);
            var_dump('boss task complete success');
        } catch (ActivitiException\ActivitiException $e) {
            var_dump($e->getMessage());
        }
    }

    // 流程启动
    public function start_post()
    {
        // return json_decode("", true);

        $client = new Client([
            'base_uri' => 'http://localhost:8080/activiti-rest/service/',
            'auth' => [
                'lily', 'lily', // start process initiator 变量设置，这样需要驳回调整申请的时候可以使用此变量作为$assignee
            ],
        ]);
        // start Process
        $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());

        $data = new ProcessInstanceCreate();
        // Only one of processDefinitionId, processDefinitionKey or message should be set.
        $data->setProcessDefinitionId(null); // 'leave_model_key:2:5078'
        $data->setProcessDefinitionKey('leave'); // 
        $data->setBusinessKey(null);
        $data->setMessage(null);
        $data->setTenantId(null);

        // $data->setVariables([
        //     new VariableCreate('days', 'integer', 10),
        //     new VariableCreate('start', 'string', '2020-04-01'),
        //     new VariableCreate('end', 'string', '2020-04-05')
        //     // new VariableCreate('intProcVar', 'integer', 123)
        //     // new VariableCreate('intProcVar', 'string', 'hello')
        //     // boolean
        // ]);

        $processInstance = $serviceFactory->createProcessInstanceService()->start($data);
        var_dump($processInstance);
        var_dump($processInstance->getId());
        var_dump($processInstance->getProcessDefinitionKey());
        var_dump($processInstance->getCompleted());

        // start 时流程变量还未生成，所以为空，后面根据流程id 可获取变量list，与taskService.complete 里设置的变量一样
        var_dump($processInstance->getVariables());

        $processInstanceVariables = $serviceFactory->createProcessInstanceService()->getVariables($processInstance->getId());
        // var_dump($processInstanceVariables);
        foreach ($processInstanceVariables as $i => $variable) {
            vprintf("%s %s (%s)\n", [
                $variable->getName(),
                $variable->getValue(),
                $variable->getType(),
            ]);
        }

        // 根据流程id，及变量名获取变量信息
        $initiator = $serviceFactory->createProcessInstanceService()->getVariable($processInstance->getId(), 'initiator');
        vprintf("%s %s (%s)\n", [
            $initiator->getName(),
            $initiator->getValue(),
            $initiator->getType(),
        ]);
        // $days = $serviceFactory->createProcessInstanceService()->getVariable($processInstance->getId(),'days');
        // $start = $serviceFactory->createProcessInstanceService()->getVariable($processInstance->getId(),'start');
        // $end = $serviceFactory->createProcessInstanceService()->getVariable($processInstance->getId(),'end');

        // string(4) "2952"
        // string(15) "leave_model_key"
        // object(Activiti\Client\Model\ProcessInstance\ProcessInstance)#59 (1) {
        //     ["data":"Activiti\Client\Model\ProcessInstance\ProcessInstance":private]=>
        //     array(13) {
        //       ["id"]=>
        //       string(4) "3348"
        //       ["url"]=>
        //       string(74) "http://localhost:8080/activiti-rest/service/runtime/process-instances/3348"
        //       ["businessKey"]=>
        //       NULL
        //       ["suspended"]=>
        //       bool(false)
        //       ["ended"]=>
        //       bool(false)
        //       ["processDefinitionId"]=>
        //       string(23) "leave_model_key:15:5300"
        //       ["processDefinitionUrl"]=>
        //       string(98) "http://localhost:8080/activiti-rest/service/repository/process-definitions/leave_model_key:15:5300"
        //       ["processDefinitionKey"]=>
        //       string(15) "leave_model_key"
        //       ["activityId"]=>
        //       NULL
        //       ["variables"]=>
        //       array(0) {
        //       }
        //       ["tenantId"]=>
        //       string(0) ""
        //       ["name"]=>
        //       NULL
        //       ["completed"]=>
        //       bool(false)
        //     }
        //   }
    }

    // 获取流程当前位置及流程图
    public function nodeposition_post()
    {
        $client = new Client([
            'base_uri' => 'http://localhost:8080/activiti-rest/service/',
            'auth' => [
                'lily', 'lily', // 还是有关系的 start process 时 标注 starter 可看到
            ],
        ]);
        $serviceFactory = new ServiceFactory($client, new ModelFactory(), new ObjectSerializer());

        $processInstanceId = 20034;

        $query = new TaskQuery();
        $query->setProcessInstanceId($processInstanceId);
        $taskList = $serviceFactory->createTaskService()->queryTasks($query);

        //  仅有一个当前值？
        foreach ($taskList as $i => $task) {
            vprintf("%s %s (Assignee: %s) %s\n", [
                $task->getName(),
                $task->getId(),
                $task->getAssignee(),
                $task->getCreateTime(),
            ]);
        }
        // var_dump($taskList->getIterator()[0]);
        // var_dump($taskList);
        try {
            // getDiagram 获得二进制流图片 可显示流程进度
            $diagramBinary = $serviceFactory->createProcessInstanceService()->getDiagram($processInstanceId);
            $base64 = 'data:image/png' . ';base64,' . base64_encode($diagramBinary);
            echo '<img src="' .  $base64 . '" />'; // binary image to base64 so postman can preview image
        } catch (ActivitiException\ActivitiException $e) {
            var_dump($e->getMessage());
        }
    }
} // class Article end
