<?php

namespace Corals\Utility\Comment\Transformers\API;

use Corals\Foundation\Transformers\FractalPresenter;

class CommentPresenter extends FractalPresenter
{
    /**
     * @return CommentTransformer
     */
    public function getTransformer()
    {
        return new CommentTransformer();
    }
}
