<?php

namespace Illuminate\Tests\Integration\Database\SchemaTest;

use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Tests\Integration\Database\DatabaseTestCase;
use Illuminate\Tests\Integration\Database\Fixtures\TinyInteger;

class SchemaBuilderTest extends DatabaseTestCase
{
    public function testDropAllTables()
    {
        $this->expectNotToPerformAssertions();

        Schema::create('table', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::dropAllTables();

        Schema::create('table', function (Blueprint $table) {
            $table->increments('id');
        });
    }

    public function testDropAllViews()
    {
        $this->expectNotToPerformAssertions();

        DB::statement('create view foo (id) as select 1');

        Schema::dropAllViews();

        DB::statement('create view foo (id) as select 1');
    }

    public function testRegisterCustomDoctrineType()
    {
        Schema::registerCustomDoctrineType(TinyInteger::class, TinyInteger::NAME, 'TINYINT');

        Schema::create('test', function (Blueprint $table) {
            $table->string('test_column');
        });

        $blueprint = new Blueprint('test', function (Blueprint $table) {
            $table->tinyInteger('test_column')->change();
        });

        $blueprint->build($this->getConnection(), new SQLiteGrammar);

        $this->assertArrayHasKey(TinyInteger::NAME, Type::getTypesMap());
        $this->assertSame('tinyinteger', Schema::getColumnType('test', 'test_column'));
    }
}
