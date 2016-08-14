<?php
/**
 * Created by solly [11.08.16 5:34]
 */

namespace insolita\migrik\tests\unit;


use Codeception\Specify;
use Codeception\Verify;
use insolita\migrik\resolver\FluentColumnResolver;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
use yii\db\Expression;
use yii\db\Schema;
use yii\db\TableSchema;

/**
 * @var Verify
 **/
class FluentColumnResolverTest extends DbTestCase
{
    use Specify;

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function fixtures()
    {
        return [

        ];
    }

    public function testResolveString()
    {
        $test = [
            [
                'col' => new ColumnSchema(
                    ['type' => Schema::TYPE_TEXT, 'allowNull' => false, 'dbType' => 'text', 'size' => 1000]
                ),
                'expect' => '$this->text(1000)->notNull()'
            ],
            [
                'col' => new ColumnSchema(
                    [
                        'type' => Schema::TYPE_TEXT,
                        'allowNull' => false,
                        'defaultValue' => 'blabla',
                        'dbType' => 'text'
                    ]
                ),
                'expect' => '$this->text()->notNull()->default("blabla")'
            ],
            [
                'col' => new ColumnSchema(
                    [
                        'type' => Schema::TYPE_STRING,
                        'allowNull' => true,
                        'comment' => 'Some comment',
                        'dbType' => 'char'
                    ]
                ),
                'expect' => '$this->string()->null()->comment("Some comment")'
            ],

        ];

        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $cbuilder = $this->getMockBuilder(ColumnSchemaBuilder::class)->disableOriginalConstructor()->getMock();
            $resolver = new FluentColumnResolver($tschema, $cbuilder, $schema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }

    public function testResolvePk()
    {
        $test = [
            [
                'col' => new ColumnSchema(
                    ['type' => Schema::TYPE_PK, 'allowNull' => true, 'dbType' => 'string', 'size' => 1000]
                ),
                'expect' => '$this->pk()'
            ],
            [
                'col' => new ColumnSchema(['type' => Schema::TYPE_UBIGPK, 'comment' => 'It`s really big']),
                'expect' => '$this->'.Schema::TYPE_UBIGPK . "()->comment('It`s really big')"
            ]

        ];

        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $cbuilder = $this->getMockBuilder(ColumnSchemaBuilder::class)->disableOriginalConstructor()->getMock();
            $resolver = new ColumnResolver($tschema, $cbuilder, $schema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }

    public function testResolveNumeric()
    {
        $test = [
            [
                'col' => new ColumnSchema(
                    [
                        'type' => Schema::TYPE_BOOLEAN,
                        'allowNull' => true,
                        'dbType' => 'bool',
                        'defaultValue' => true
                    ]
                ),
                'expect' => '$this->boolean()->default(true)'
            ],
            [
                'col' => new ColumnSchema(
                    [
                        'type' => Schema::TYPE_BOOLEAN,
                        'allowNull' => false,
                        'dbType' => 'bool',
                        'defaultValue' => false
                    ]
                ),
                'expect' => '$this->boolean()->notNull()->default(false)'
            ],
            [
                'col' => new ColumnSchema(
                    [
                        'type' => Schema::TYPE_BOOLEAN,
                        'dbType' => 'bool'
                    ]
                ),
                'expect' => '$this->boolean()->notNull()'
            ],

            [
                'col' => new ColumnSchema(
                    [
                        'type' => Schema::TYPE_DECIMAL,
                        'scale' => 8,
                        'precision' => 2,
                        'defaultValue' => 340.23,
                        'dbType' => 'decimal'
                    ]
                ),
                'expect' => '$this->decimal(8, 2)->notNull()->default(340.23)'
            ],
            [
                'col' => new ColumnSchema(
                    [
                        'type' => Schema::TYPE_FLOAT,
                        'precision' => 3,
                        'defaultValue' => 340.213,
                        'unsigned' => true,
                        'dbType' => 'float'
                    ]
                ),
                'expect' => '$this->float(, 3)->unsigned()->notNull()->default(340.213)'
            ],

        ];

        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $cbuilder = $this->getMockBuilder(ColumnSchemaBuilder::class)->disableOriginalConstructor()->getMock();
            $resolver = new ColumnResolver($tschema, $cbuilder, $schema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }

    public function testResolveTime()
    {
        $test = [
            [
                'col' => new ColumnSchema(
                    [
                        'type' => Schema::TYPE_DATE,
                        'allowNull' => false,
                        'dbType' => 'date',
                        'defaultValue' => 'CURRENT_DATE'
                    ]
                ),
                'expect' =>'$this->date()->notNull()->defaultExpression("CURRENT_DATE")'
            ],
            [
                'col' => new ColumnSchema(
                    [
                        'type' => Schema::TYPE_DATETIME,
                        'allowNull' => false,
                        'precision' => 0,
                        'dbType' => 'datetime',
                        'defaultValue' => new Expression('NOW()')
                    ]
                ),
                'expect' => '$this->datetime(0)->notNull()->defaultExpression("NOW()")'
            ]
        ];

        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $cbuilder = $this->getMockBuilder(ColumnSchemaBuilder::class)->disableOriginalConstructor()->getMock();
            $resolver = new ColumnResolver($tschema, $cbuilder, $schema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }

    public function testResolveEnumType()
    {
        $test = [
            [
                'col' => new ColumnSchema(
                    [
                        'type' => Schema::TYPE_STRING,
                        'allowNull' => true,
                        'dbType' => 'enum',
                        'enumValues' => ['one', 'two', 'three'],
                        'defaultValue' => 'two'
                    ]
                ),
                'expect' => '$this->string()->null()->default("two")'
            ]

        ];

        foreach ($test as $testItem) {
            $schema = \Yii::$app->getDb()->getSchema();
            $tschema = $this->getMockBuilder(TableSchema::class)->getMock();
            $tschema->expects($this->once())->method('getColumn')->willReturn($testItem['col']);
            $cbuilder = $this->getMockBuilder(ColumnSchemaBuilder::class)->disableOriginalConstructor()->getMock();
            $resolver = new ColumnResolver($tschema, $cbuilder, $schema);
            $string = $resolver->resolveColumn('col');
            verify($string)->equals($testItem['expect']);
        }
    }
}