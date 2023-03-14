<?php

namespace Tests\Feature;

use Corals\Settings\Facades\Modules;
use Corals\User\Models\User;
use Corals\Utility\Comment\Models\Comment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UtilityCommentTest extends TestCase
{
    use DatabaseTransactions;

    protected $comment;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $user = User::query()->whereHas('roles', function ($query) {
            $query->where('name', 'member')->whereHas('permissions', function ($queryRole) {
                $queryRole->where('name', 'Utility::comment.create');
            });
        })->first();
        Auth::loginUsingId($user->id);
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
                            $comments = ["Im happy", "Hey people", "Good", "Nice"];
                            $response = $this->post($array['prefix'] . '/' . $model->hashed_id . '/create-comment', [
                                'body' => array_rand($comments),
                            ]);

                            $this->comment = Comment::query()->first();
                            $response->assertDontSee('The given data was invalid')
                                ->assertSeeText('Your comment has been added successfully');

                            $names = ["Ali", "Nour", "Susan", "Tom"];
                            $email = ["ali@comment.com", "nour@comment.com", "susan@comment.com", "tom@comment.com"];
                            $response2 = $this->post(
                                $array['prefix'] . '/' . $this->comment->hashed_id . '/create-reply',
                                [
                                    'properties' => [
                                        'author_name' => array_rand($names),
                                        'author_email' => array_rand($email),
                                    ],
                                    'body' => array_rand($comments),
                                ]
                            );

                            $response2->assertDontSee('The given data was invalid')
                                ->assertSeeText('Your comment has been added successfully');

                            $this->assertDatabaseHas('utility_comments', [
                                'body' => $this->comment->body,
                                'commentable_type' => $this->comment->commentable_type,
                                'commentable_id' => $this->comment->commentable_id,
                            ]);
                        }
                    }
                }
            }
        }
        $this->assertFalse(false);
    }

    public function test_utility_comment_toggle_status()
    {
        $this->test_utility_comment_create_and_reply();

        if ($this->comment) {
            $response = $this->post('utilities/comments/' . $this->comment->hashed_id . '/pending');

            $response->assertStatus(200)->assertSeeText('Comment status has been update successfully');
        }
        $this->assertTrue(true);
    }

    public function test_utility_comment_delete()
    {
        $this->test_utility_comment_create_and_reply();

        $user = User::query()->whereHas('roles', function ($query) {
            $query->where('name', 'superuser');
        })->first();
        Auth::loginUsingId($user->id);

        if ($this->comment) {
            $response = $this->delete('utilities/comments/' . $this->comment->hashed_id);

            $response->assertStatus(200)->assertSeeText('Comment has been deleted successfully.');

            $this->isSoftDeletableModel(Comment::class);
            $this->assertDatabaseMissing('utility_comments', [
                'body' => $this->comment->body,
                'commentable_type' => $this->comment->commentable_type,
                'commentable_id' => $this->comment->commentable_id,
            ]);
        }
        $this->assertTrue(true);
    }
}
