<?php

namespace Tests\Feature;

use App\Http\Controllers\Controller;
use App\Models\SystemMedia;
use App\Models\SystemMediaCategory;
use Illuminate\Http\UploadedFile;
use Tests\AdminTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\RequestActions;

class SystemMediaControllerTest extends AdminTestCase
{
    use RequestActions;
    use RefreshDatabase;
    protected $resourceName = 'system-media';

    protected function setUp(): void
    {
        parent::setUp();
        $this->login();
    }

    public function testDestroy()
    {
        factory(SystemMediaCategory::class)->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $this->storeResource(
            [
                'file' => $file,
                Controller::UPLOAD_FOLDER_FIELD => 'tests',
            ],
            'system-media-categories.system-media',
            ['id' => 1]
        );

        $fileFullPath = public_path(Controller::UPLOAD_FOLDER_PREFIX.'/tests/'.md5_file($file).'.jpg');
        $this->assertFileExists($fileFullPath);
        // 复制一条记录
        factory(SystemMedia::class)->create(tap(SystemMedia::find(1))->addHidden(['id'])->toArray());

        $res = $this->destroyResource(1);
        $res->assertStatus(204);

        // 有重复文件记录，所以不需要删除物理文件
        $this->assertFileExists($fileFullPath);
        $this->assertDatabaseMissing('system_media', [
            'id' => 1,
        ]);

        $res = $this->destroyResource(2);
        $res->assertStatus(204);
        // 没有重复文件记录，删除物理文件
        $this->assertFileNotExists($fileFullPath);
        $this->assertDatabaseMissing('system_media', [
            'id' => 2,
        ]);
    }

    public function testEdit()
    {
        $media = factory(SystemMedia::class)->create();

        $res = $this->editResource(1);
        $res->assertStatus(200)
            ->assertJsonFragment([
                'id' => 1,
                'category_id' => 0,
                'url' => $this->storage->url($media->path),
            ]);
    }

    public function testUpdate()
    {
        factory(SystemMediaCategory::class)->create();
        factory(SystemMedia::class)->create();

        // category_id exists
        $res = $this->updateResource(1, [
            'category_id' => 999,
        ]);
        $res->assertJsonValidationErrors(['category_id']);

        $res = $this->updateResource(1, [
            'category_id' => 1,
        ]);
        $res->assertStatus(201);

        $this->assertDatabaseHas('system_media', [
            'id' => 1,
            'category_id' => 1,
        ]);
    }

    public function testBatchUpdate()
    {
        factory(SystemMediaCategory::class)->create();
        factory(SystemMedia::class, 2)->create();

        $res = $this->put(route('admin.system-media.batch.update'), [
            'category_id' => 1,
            'id' => [1, 2],
        ]);
        $res->assertStatus(201);

        $this->assertDatabaseHas('system_media', [
            'id' => 1,
            'category_id' => 1,
        ]);
        $this->assertDatabaseHas('system_media', [
            'id' => 2,
            'category_id' => 1,
        ]);
    }

    public function testBatchDestroy()
    {
        factory(SystemMedia::class, 2)->create();

        $res = $this->delete(route('admin.system-media.batch.destroy'), [
            'id' => [1, 2],
        ]);
        $res->assertStatus(204);

        $this->assertDatabaseMissing('system_media', ['id' => 1]);
        $this->assertDatabaseMissing('system_media', ['id' => 2]);
    }
}
