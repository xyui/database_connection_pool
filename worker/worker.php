<?php
include('../conf/PoolConf.php');
include('../lib/Pool/Server.php');

# Create our worker object.
$worker= new GearmanWorker();
foreach (PoolConf::$SERVERS as $server) {
    $worker->addServer($server[0], $server[1]);
}

\Pool\Server::init();

$worker->addFunction('getConnection', 'getConnection');
$worker->addFunction('prepare', 'prepare');
$worker->addFunction('execute', 'execute');
$worker->addFunction('fetchAll', 'fetchAll');
$worker->addFunction('info', 'info');
$worker->addFunction('release', 'release');
$worker->addFunction('beginTransaction', 'beginTransaction');
$worker->addFunction('commit', 'commit');
$worker->addFunction('rollback', 'rollback');

while (1) {
    try {
        $worker->work();
        if ($worker->returnCode() != GEARMAN_SUCCESS) {
            break;
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function getConnection ($job) {
    echo __function__."\n";
    // $job->workload() 接收数据
    $key = $job->workload(); // PoolConf::PG1

    return \Pool\Server::getConnection($key);
}

function prepare ($job) {
    echo __function__."\n";
    // $job->workload() 接收数据
    $params = unserialize($job->workload());
    $sql = $params['sql'];
    $cid = $params['cid'];
    $key = $params['key'];
    return \Pool\Server::prepare($key, $cid, $sql);
}

function execute ($job) {
    echo __function__."\n";
    // $job->workload() 接收数据
    $params = unserialize($job->workload());
    $stmtId = $params['stmt'];
    $parameters = $params['parameters'];
    return \Pool\Server::execute($stmtId, $parameters);
}

function fetchAll ($job) {
    echo __function__."\n";
    $params = unserialize($job->workload());
    $stmtId = $params['stmt'];
    return serialize(\Pool\Server::fetchAll($stmtId));
}

function info ($job) {
    echo __function__."\n";
    $key = $job->workload();
    return serialize(\Pool\Server::info($key));
}

function release ($job) {
    echo __function__."\n";
    $params = unserialize($job->workload());
    $cid = $params['cid'];
    $key = $params['key'];
    return serialize(\Pool\Server::release($key, $cid));
}

function beginTransaction ($job) {
    echo __function__."\n";
    $params = unserialize($job->workload());
    $cid = $params['cid'];
    $key = $params['key'];

    return \Pool\Server::beginTransaction($cid, $key);
}

function commit ($job) {
    echo __function__."\n";
    $params = unserialize($job->workload());
    $cid = $params['cid'];
    $key = $params['key'];

    return \Pool\Server::commit($cid, $key);
}

function rollback ($job) {
    echo __function__."\n";
    $params = unserialize($job->workload());
    $cid = $params['cid'];
    $key = $params['key'];

    return \Pool\Server::rollback($cid, $key);
}

