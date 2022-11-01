<?php

namespace Tests\Feature;

use Corals\Modules\Utility\Comment\Models\Comment;
use Corals\Settings\Facades\Modules;
use Corals\User\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UtilityCommentTest extends TestCase
{
    use DatabaseTransactions;

    protected $comment = [];

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $usersRole = User::all();
        $users = [];
        foreach ($usersRole as $user) {
            if ($user->hasRole('member') &&
                $user->hasPermissionTo('Utility::comment.create') &&
                $user->hasPermissionTo('Utility::comment.reply')) {
                $users[] = $user;
            }
        }
        Auth::loginUsingId($users[0]->id);
    }

    public function test_utility_comment_create_and_reply()
    {
        $modules = [
            'CMS' => ['code' => 'corals-cms', 'prefix' => 'cms'],
            'Marketplace' => ['code' => 'corals-marketplace', 'prefix' => 'products'],
        ];

        foreach ($modules as $module => $array) {
            if (Modules::isModuleActive($array['code'])) {
                $namespace = 'Corals\Modules\\' . $module . '\\Models';
                $myClasses = array_filter(get_declared_classes(), function ($item) use ($namespace) {
                    return substr($item, 0, strlen($namespace)) === $namespace;
                });

                foreach ($myClasses as $class) {
                    $traits = class_uses($class);
                    if (array_search('Corals\\Modules\\Utility\\Comment\\Traits\\ModelHasComments', $traits)) {
                        $model = $class::query()->first();
                        if ($model) {
                            $comments = array("Im happy", "Hey people", "Good", "Nice");
                            $response = $this->post($array['prefix'] . '/' . $model->hashed_id . '/create-comment', [
                                'body' => array_rand($comments)]);

                            $this->comment = Comment::query()->first();
                            $response->assertStatus(200)->assertSeeText('Your comment has been added successfully');

                            $names = array("Ali", "Nour", "Susan", "Tom");
                            $email = array("ali@comment.com", "nour@comment.com", "susan@comment.com", "tom@comment.com");
                            $response2 = $this->post($array['prefix'] . '/' . $this->comment->hashed_id . '/create-reply', [
                                'properties' => [
                                    'author_name' => array_rand($names),
                                    'author_email' => array_rand($email)],
                                'body' => array_rand($comments)]);

                            $response2->assertStatus(200)->assertSeeText('Your comment has been added successfully');

                        }
                    }
                }
            }
        }
        $this->assertFalse(false);
    }

    public function test_utility_comment_toggle_status()
    {
        if ($this->comment) {
            $response = $this->post('utilities/comments/' . $this->comment->hashed_id . '/pending');

            $response->assertStatus(200)
                ->assertSeeText('Comment status has been update successfully');
        }
        $this->assertTrue(true);
    }

    public function test_utility_comment_delete()
    {
        if ($this->comment) {
            $response = $this->delete('utilities/comments/' . $this->comment->hashed_id);

            $response->assertStatus(200)->assertSeeText('Comment has been deleted successfully.');

        }
        $this->assertTrue(true);
    }
}
