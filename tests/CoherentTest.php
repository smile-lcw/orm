<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/23 0023
 * Time: 22:00
 */

namespace EasySwoole\ORM\Tests;



namespace EasySwoole\ORM\Tests;
use EasySwoole\ORM\Db\Config;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use PHPUnit\Framework\TestCase;
class CoherentTest extends TestCase
{
    /**
     * @var $connection Connection
     */
    protected $connection;
    protected $tableName = 'user_test_list';
    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $config = new Config(MYSQL_CONFIG);
        $this->connection = new Connection($config);
        DbManager::getInstance()->addConnection($this->connection);
        $connection = DbManager::getInstance()->getConnection();
        $this->assertTrue($connection === $this->connection);
    }
    public function testAdd()
    {
         $testUserModel = new TestUserListModel();
         $testUserModel->state = 1;
         $testUserModel->name = '仙士可';
         $testUserModel->age = 100;
         $testUserModel->addTime = date('Y-m-d H:i:s');
         $data = $testUserModel->save();
         $this->assertIsInt($data);

         $testUserModel = new TestUserListModel();
         $testUserModel->state = 2;
         $testUserModel->name = 'Siam';
         $testUserModel->age = 18;
         $testUserModel->addTime = date('Y-m-d H:i:s');
         $data = $testUserModel->save();
         $this->assertIsInt($data);

         $testUserModel = new TestUserListModel();
         $testUserModel->state = 2;
         $testUserModel->name = 'Siam';
         $testUserModel->age = 19;
         $testUserModel->addTime = date('Y-m-d H:i:s');
         $data = $testUserModel->save();
         $this->assertIsInt($data);
    }

    public function testWhere()
    {
        $testUserModel = TestUserListModel::create();
        $get = $testUserModel->get([
         'state' => 1
        ]);
        $model =  TestUserListModel::create();
        $getCoherent = $model->where(['state' => 1])->get();

        $getCoherent2Model = TestUserListModel::create();
        $getCoherent2      = $getCoherent2Model->where(['state' => 2])->get();

        $this->assertEquals($get->age, $getCoherent->age);
        $this->assertNotEquals($get->age, $getCoherent2->age);

        $getCoherent3 = $model->where($getCoherent2->id)->get();
        $this->assertEquals($getCoherent3->age, $getCoherent3->age);
    }

    public function testGroupAndAll()
    {
        $group = TestUserListModel::create()->field('sum(age) as age, `name`')->group('name')->all(null);

        foreach ($group as $one){
            if ($one->name == 'Siam'){
                $this->assertEquals($one->age, 18+19);
            }else{
                $this->assertEquals($one->age, 100);
            }
        }
    }

    public function testOrder()
    {
        $order = TestUserListModel::create()->order('id', 'DESC')->get();

        $this->assertEquals($order->age, 19);
    }

    public function testSelect()
    {
        $groupDivField = TestUserListModel::create()->field('sum(age), `name`')->group('name')->select();

        $this->assertNotEmpty($groupDivField[0]['sum(age)']);
    }

    public function testJoin()
    {

    }

    public function testMax()
    {
        $max = TestUserListModel::create()->max('age');
        $this->assertEquals($max, 100);
    }

    public function testMin()
    {
        $min = TestUserListModel::create()->min('age');
        $this->assertEquals($min, 18);
    }

    public function testCount()
    {
        $count = TestUserListModel::create()->count();
        $this->assertEquals($count, 3);
    }

    public function testAvg()
    {
        $avg = TestUserListModel::create()->avg('age');
        $this->assertEquals($avg, 45.6667);
    }
    public function testSum()
    {
        $sum = TestUserListModel::create()->sum('age');
        $this->assertEquals($sum, 100+18+19);
    }

    public function testDeleteAll()
    {
        $res = TestUserListModel::create()->destroy(null, true);
        $this->assertIsInt($res);
    }
}