<?php

/**
 * This file is part of the Lasalle Software blog front-end package
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright  (c) 2019 The South LaSalle Trading Corporation
 * @license    http://opensource.org/licenses/MIT
 * @author     Bob Bloom
 * @email      bob.bloom@lasallesoftware.ca
 * @link       https://lasallesoftware.ca
 * @link       https://packagist.org/packages/lasallesoftware/lsv2-blogfrontend-pkg
 * @link       https://github.com/LaSalleSoftware/lsv2-blogfrontend-pkg
 *
 */

Route::get('blog/feed',                     'Lasallesoftware\Blogfrontend\Http\Controllers\DisplayBlogRSSFeedController@DisplayBlogRSSFeed');
Route::get('blog/all',                      'Lasallesoftware\Blogfrontend\Http\Controllers\DisplayAllBlogPostsController@DisplayAllBlogPosts');
Route::get('category/{categoryTitle}',      'Lasallesoftware\Blogfrontend\Http\Controllers\DisplayBlogPostsForACategoryController@DisplayBlogPostsForACategory');
Route::get('author/{authorNamecalculated}', 'Lasallesoftware\Blogfrontend\Http\Controllers\DisplayBlogPostsForAnAuthorController@DisplayBlogPostsForAnAuthor');
Route::get('tag/{tagTitle}',                'Lasallesoftware\Blogfrontend\Http\Controllers\DisplayBlogPostsForATagController@DisplayBlogPostsForATag');


//$router->get('{slug}', '\Lasallecms\Lasallecmsfrontend\Http\Controllers\PostController@DisplaySinglePost')->where('slug', '!=', 'admin');

Route::get('{slug}', 'Lasallesoftware\Blogfrontend\Http\Controllers\DisplaySinglePostController@DisplaySinglePost');


