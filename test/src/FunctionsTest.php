<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

use Symfony\Component\Console\Application;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Deployer
     */
    private $deployer;

    protected function setUp()
    {
        $console = new Application();
        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $this->deployer = new Deployer($console, $input, $output);
    }

    protected function tearDown()
    {
        unset($this->deployer);
        $this->deployer = null;
    }

    public function testServer()
    {
        server('main', 'domain.com', 22);
        
        $server = $this->deployer->servers->get('main');
        $env = $this->deployer->environments->get('main');
        
        $this->assertInstanceOf('Deployer\Server\ServerInterface', $server);
        $this->assertInstanceOf('Deployer\Server\Environment', $env);
    }

    public function testServerGroups()
    {
        serverGroup('main', ['one', 'two']);

        $list = $this->deployer->serverGroups->get('main');
        $this->assertEquals(['one', 'two'], $list);
    }

    public function testTask()
    {
        task('task', function () {});

        $task = $this->deployer->tasks->get('task');
        $this->assertInstanceOf('Deployer\Task\Task', $task);

        task('group', ['task']);
        $task = $this->deployer->tasks->get('group');
        $this->assertInstanceOf('Deployer\Task\GroupTask', $task);

        $this->setExpectedException('InvalidArgumentException', 'Task should be an closure or array of other tasks.');
        task('wrong', 'thing');
    }
    
    public function testBefore()
    {
        task('main', function () {});
        task('before', function () {});
        before('main', 'before');
        
        $mainScenario = $this->deployer->scenarios->get('main');
        $this->assertInstanceOf('Deployer\Task\Scenario\Scenario', $mainScenario);
        $this->assertEquals(['before', 'main'], $mainScenario->getTasks());
    }
    
    public function testAfter()
    {
        task('main', function () {});
        task('after', function () {});
        after('main', 'after');
        
        $mainScenario = $this->deployer->scenarios->get('main');
        $this->assertInstanceOf('Deployer\Task\Scenario\Scenario', $mainScenario);
        $this->assertEquals(['main', 'after'], $mainScenario->getTasks());
    }
}
