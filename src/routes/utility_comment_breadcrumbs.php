<?php

//Comment
Breadcrumbs::register('utility_comments', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(trans('utility-comment::module.comment.title'), url(config('utility-comment.models.comment.resource_url')));
});

Breadcrumbs::register('utility_category_create_edit', function ($breadcrumbs) {
    $breadcrumbs->parent('utility_categories');
    $breadcrumbs->push(view()->shared('title_singular'));
});
