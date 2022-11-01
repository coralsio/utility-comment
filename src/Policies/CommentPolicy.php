<?php

namespace Corals\Modules\Utility\Comment\Policies;

use Corals\Foundation\Policies\BasePolicy;
use Corals\Modules\Utility\Comment\Models\Comment;
use Corals\User\Models\User;

class CommentPolicy extends BasePolicy
{
    protected $administrationPermission = 'Administrations::admin.utility_comment';

    protected $skippedAbilities = ['updateStatus', 'create'];

    public function updateStatus(User $user, Comment $comment = null, $status = null)
    {
        if ($comment->status == $status) {
            return false;
        }

        return $user->can('Utility::comment.set_status');
    }

    public function create(User $user)
    {
        if ($user->hasRole('member') && $user->can('Utility::comment.create')) {
            return true;
        }

        return false;
    }

    public function view(User $user)
    {
        return $user->can('Utility::comment.view');
    }

    public function destroy(User $user, Comment $comment)
    {
        return $user->can('Utility::comment.delete');
    }

    public function seePrivateComments(User $user, Comment $comment = null)
    {
        return $user->can('Utility::comment.can_see_private_comments')
            || ($comment && $comment->author_id == $user->id);
    }
}
