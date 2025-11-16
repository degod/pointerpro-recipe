<?php

namespace Tests\Feature\Http\Controllers\Recipe;

use App\Models\User;
use App\Repositories\Recipe\RecipeRepositoryInterface;
use App\Services\ResponseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class RecipeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected $repositoryMock;
    protected ResponseService $responseService;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->repositoryMock = Mockery::mock(RecipeRepositoryInterface::class);
        $this->app->instance(RecipeRepositoryInterface::class, $this->repositoryMock);
        $this->responseService = $this->app->make(ResponseService::class);

        Sanctum::actingAs($this->user);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testBasicTest()
    {
        $this->assertTrue(true);
    }
}
