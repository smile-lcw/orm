<?php
/**
 * 关联查询、toArray
 * User: Siam
 * Date: 2019/11/15
 * Time: 17:32
 */

namespace EasySwoole\ORM\Tests;

use EasySwoole\ORM\Db\Config;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use PHPUnit\Framework\TestCase;



use EasySwoole\ORM\Tests\models\TestUserListModel;
use EasySwoole\ORM\Tests\models\TestRelationModel;

class RelationToArrayTest extends TestCase
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
        $test_user_model = TestRelationModel::create();
        $test_user_model->name = 'siam_relation';
        $test_user_model->age = 21;
        $test_user_model->addTime = "2019-11-15 17:36:34";
        $test_user_model->state = 2;
        $test_user_model->save();

        $user_list = TestUserListModel::create();
        $user_list->name = 'siam_relation';
        $user_list->age = 21;
        $user_list->addTime = "2019-11-15 17:37:20";
        $user_list->state=1;
        $user_list->save();
    }
    
    public function testGet()
    {
        $test_user_model = TestRelationModel::create()->get([
            'name' => 'siam_relation'
        ]);
        $relation =  $test_user_model->user_list();
        $this->assertInstanceOf(TestUserListModel::class, $relation);

        $toArray = $test_user_model->toArray(false, false);
        $this->assertNotEmpty($toArray['user_list']);
        $this->assertIsArray($toArray['user_list']);
    }

    /**
     * 在invoke中触发关联
     * @throws \Throwable
     */
    public function testGetInvokeRelation()
    {
        DbManager::getInstance()->invoke(function ($client) {
            $testUserModel = TestRelationModel::invoke($client)->with('user_list')->get();
            $this->assertNotEmpty($testUserModel['user_list']);
            $this->assertInstanceOf(TestUserListModel::class, $testUserModel['user_list']);
        });
    }

    /**
     * field筛选 toArray
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function testFieldFilterToArray()
    {
        $test_user_model = TestRelationModel::create()->get([
            'name' => 'siam_relation'
        ]);
        $relation =  $test_user_model->user_list();
        $this->assertInstanceOf(TestUserListModel::class, $relation);

        $toArray = $test_user_model->field(['user_list'])->toArray(false, false);
        $this->assertEquals(1, count($toArray));
        $this->assertNotEmpty($toArray['user_list']);
        $this->assertIsArray($toArray['user_list']);
    }

    /**
     * hidden隐藏
     * @throws
     */
    public function testHiddenFilterToArray()
    {
        $test_user_model = TestRelationModel::create()->get([
            'name' => 'siam_relation'
        ]);
        $test_user_model->user_list();

        $toArray = $test_user_model->hidden('user_list')->toArray(false, false);
        $this->assertEquals(5, count($toArray));
        $this->assertArrayNotHasKey("user_list", $toArray);
    }

    public function testJson()
    {
        $test_user_model = TestRelationModel::create()->get([
            'name' => 'siam_relation'
        ]);
        $relation =  $test_user_model->user_list();
        // echo json_encode($test_user_model);
        $this->assertIsString(json_encode($test_user_model));
    }

    public function testDeleteAll()
    {
        $res = TestRelationModel::create()->destroy(null, true);
        $this->assertIsInt($res);
        $res = TestUserListModel::create()->destroy(null, true);
        $this->assertIsInt($res);
    }

    public function testAddHasMany()
    {
        $test_user_model = TestRelationModel::create();
        $test_user_model->name = 'siam';
        $test_user_model->age = 20;
        $test_user_model->addTime = "2019-11-15 17:36:34";
        $test_user_model->state = 2;
        $test_user_model->save();

        $user_list = TestUserListModel::create();
        $user_list->name = 'siam';
        $user_list->age = 22;
        $user_list->addTime = "2019-11-15 17:37:20";
        $user_list->state=1;
        $user_list->save();

        $user_list = TestUserListModel::create();
        $user_list->name = 'siam';
        $user_list->age = 21;
        $user_list->addTime = "2019-11-15 17:37:20";
        $user_list->state=1;
        $user_list->save();
    }
    public function testHasMany()
    {
        $test_user_model = TestRelationModel::create()->get([
            'name' => 'siam'
        ]);
        $hasMany =  $test_user_model->has_many();
        $this->assertEquals(2, count($hasMany));
        $this->assertInstanceOf(TestUserListModel::class, $hasMany[1]);
    }

    public function testHasManyWhere()
    {
        $test_user_model = TestRelationModel::create()->get([
            'name' => 'siam'
        ]);
        $hasMany =  $test_user_model->has_many_where();
        $this->assertEquals(1, count($hasMany));
        $this->assertEquals(21, $hasMany[0]->age);
        $this->assertInstanceOf(TestUserListModel::class, $hasMany[0]);
    }

    public function testGetWith()
    {
        $test = TestRelationModel::create()->with(['user_list', 'has_many'])->get([
            'name' => 'siam'
        ]);
        $this->assertNotEmpty($test['user_list']);
        $this->assertInstanceOf(TestUserListModel::class, $test['user_list']);

        $this->assertEquals(2, count($test['has_many']));
        $this->assertInstanceOf(TestUserListModel::class, $test['has_many'][1]);
    }

    public function testAllWith()
    {
        $test = TestRelationModel::create()->with(['user_list', 'has_many'])->all();
        $this->assertNotEmpty($test[0]['user_list']);
        $this->assertInstanceOf(TestUserListModel::class, $test[0]['user_list']);

        $this->assertEquals(2, count($test[0]['has_many']));
        $this->assertInstanceOf(TestUserListModel::class, $test[0]['has_many'][1]);
    }

    public function testDeleteAllHasMany()
    {
        $res = TestRelationModel::create()->destroy(null, true);
        $this->assertIsInt($res);
        $res = TestUserListModel::create()->destroy(null, true);
        $this->assertIsInt($res);
    }

}