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
 * @copyright  (c) 2019-2020 The South LaSalle Trading Corporation
 * @license    http://opensource.org/licenses/MIT
 * @author     Bob Bloom
 * @email      bob.bloom@lasallesoftware.ca
 * @link       https://lasallesoftware.ca
 * @link       https://packagist.org/packages/lasallesoftware/ls-blogfrontend-pkg
 * @link       https://github.com/LaSalleSoftware/ls-blogfrontend-pkg
 *
 */

namespace Lasallesoftware\Blogfrontend\Http\Controllers;

// LaSalle Software
use Lasallesoftware\Libraryfrontend\APIRequestsToTheBackend\HttpRequestToAdminBackend;

// Laravel Framework
use Illuminate\Support\MessageBag;

// Third party classes
use GuzzleHttp\Exception\RequestException;


class DisplayHomepageBlogPostsController extends BaseFrontendController
{
    use HttpRequestToAdminBackend;

    /**
     * The message bag instance.
     *
     * @var \Illuminate\Support\MessageBag
     */
    protected $messages;


    public function DisplayHomepageBlogPosts()
    {
        $endpointPath = $this->getEndpointPath('DisplayHomepageBlogPostsController');
        $httpRequest  = 'POST';

        $postData = [
            'podcast_shows'              => config('lasallesoftware-libraryfrontend.lasalle_podcast_shows_to_display_on_the_home_page'),
            'number_of_podcast_episodes' => config('lasallesoftware-libraryfrontend.lasalle_number_of_recent_podcast_episodes_to_display_on_the_home_page'),
            'video_shows'                => config('lasallesoftware-libraryfrontend.lasalle_video_shows_to_display_on_the_home_page'),
            'number_of_video_episodes'   => config('lasallesoftware-libraryfrontend.lasalle_number_of_recent_video_episodes_to_display_on_the_home_page'),            
        ];

        $response = $this->sendRequestToLasalleBackend($endpointPath, $httpRequest, null, $postData);

        if (!isset($this->messages)) {
            $body = json_decode($response->getBody());

            $transformedPosts           = $this->getTransformedPosts($body->posts);
            $transformedPodcastEpisodes = $this->getTransformedPodcastEpisodes($body->podcast_episodes);
            $transformedVideoEpisodes   = $this->getTransformedVideoEpisodes($body->video_episodes);

        } else {
            $transformedPosts = false;
        }

        return view(config('lasallesoftware-libraryfrontend.lasalle_path_to_front_end_view_path') . '.home', [
            'posts'                                => $transformedPosts,
            'numberOfPosts'                        => ($transformedPosts) ? count($transformedPosts) : 0,
            'podcast_episodes'                     => $transformedPodcastEpisodes,
            'video_episodes'                       => $transformedVideoEpisodes,
            'copyright'                            => env('LASALLE_COPYRIGHT_IN_FOOTER'),
            'socialMediaMetaTags'                  => $this->getSocialMediaMetaTags(),
            'featured_image_social_media_meta_tag' => config('lasallesoftware-libraryfrontend.lasalle_social_media_meta_tag_default_image'),
            'question'                             => $this->getSecurityQuestion(),
        ]);
    }



    private function getTransformedPosts($posts)
    {
        $transformedPosts = [];

        foreach ($posts as $post) {
            $transformedPost = [
                'title'               => $post->title,
                'slug'                => $post->slug,
                'author'              => $post->author,
                'excerpt'             => $post->excerpt,
                'featured_image'      => $this->getFeaturedImage($post->featured_image),
                'featured_image_type' => $this->getFeaturedImageType($post->featured_image_type),
                'publish_on'          => $this->formatDate($post->publish_on),
                'datetime'            => $this->formatDateForHTMLTimeTag($post->publish_on),
            ];

            $transformedPosts[] = $transformedPost;
        }

        return $transformedPosts;
    }


    private function getTransformedPodcastEpisodes($podcastEpisodesForAllPodcastShows)
    {
        // The podcast show array contains its episodes to be displayed on the home page.
        // So there are two nested foreach statements: the foreach show, and within this the foreach episodes.

        // Cycle through each podcast show
        $transformedPodcastEpisodesForAllPodcastShows = [];
        foreach ($podcastEpisodesForAllPodcastShows as $podcastEpisodeForAPodcastShow) {

            // cycle through each podcast show's episodes, transforming each episode one-by-one
            $transformedPodcastEpisodeForAPodcastShow = [];
            foreach ($podcastEpisodeForAPodcastShow as $podcastEpisode) {

                $transformedEpisode = [
                    'podcast_show_id'         => $podcastEpisode->podcast_show_id,
                    'title'                   => $podcastEpisode->title,
                    'website_excerpt'         => $podcastEpisode->website_excerpt,
                    'website_featured_image'  => $podcastEpisode->website_featured_image,
                    'itunes_link'             => $podcastEpisode->itunes_link,
                    'website_publish_on'      => $this->formatDate($podcastEpisode->website_publish_on),
                    ];                
            
                // add the transformed episode to the podcast show's array
                $transformedPodcastEpisodeForAPodcastShow[] = $transformedEpisode;
            }

            // Add all the transformed episodes to (what is essentially) the podcast show "outer" array.
            // So we've recreated the same nested array structure that we started with, except now we have transformed episodes.
            $transformedPodcastEpisodesForAllPodcastShows[] = $transformedPodcastEpisodeForAPodcastShow;
        }

        return $transformedPodcastEpisodesForAllPodcastShows;
    }


    private function getTransformedVideoEpisodes($videoEpisodesForAllVideoShows)
    {
        // The video show array contains its episodes to be displayed on the home page.
        // So there are two nested foreach statements: the foreach show, and within this the foreach episodes.

        // Cycle through each video show
        $transformedVideoEpisodesForAllVideoShows = [];
        foreach ($videoEpisodesForAllVideoShows as $videoEpisodeForAVideoShow) {

            // cycle through each video show's episodes, transforming each episode one-by-one
            $transformedVideoEpisodeForAVideoShow = [];
            foreach ($videoEpisodeForAVideoShow as $videoEpisode) {

                $transformedEpisode = [
                    'video_show_id'           => $videoEpisode->video_show_id,
                    'title'                   => $videoEpisode->title,
                    'website_excerpt'         => $videoEpisode->website_excerpt,
                    'website_featured_image'  => $videoEpisode->website_featured_image,
                    'website_publish_on'      => $this->formatDate($videoEpisode->website_publish_on),
                    ];                
            
                // add the transformed episode to the podcast show's array
                $transformedVideoEpisodeForAVideoShow[] = $transformedEpisode;
            }

            // Add all the transformed episodes to (what is essentially) the video show "outer" array.
            // So we've recreated the same nested array structure that we started with, except now we have transformed episodes.
            $transformedVideoEpisodesForAllVideoShows[] = $transformedVideoEpisodeForAVideoShow;
        }        

        return $transformedVideoEpisodesForAllVideoShows;
    }
    

    /**
     * Get the social media meta tags.
     *
     * @return array
     */
    private function getSocialMediaMetaTags()
    {
        // ...put all the social media meta tag info together
        // https://developer.twitter.com/en/docs/tweets/optimize-with-cards/guides/getting-started
        // https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/summary-card-with-large-image
        // https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/markup
        // https://ogp.me/
        return [
            'twitter_card' => 'summary_large_image',
            'og_type'      => 'website',
            'title'        => config('app.name'),
            'description'  => config('app.name') . ' home page',
            'url'          => url()->full(),
            'site'         => $this->getSocialMediaMetaTagSite(),
            'creator'      => $this->getSocialMediaMetaTagCreator(),
            'image'        => config('lasallesoftware-libraryfrontend.lasalle_social_media_meta_tag_default_image'),
        ];
    }

    /**
     * Get the contact form's security question
     *
     * @return array | null
     */
    private function getSecurityQuestion()
    {
        if (class_exists('\Lasallesoftware\Contactformfrontend\SecurityQuestionhelper')) {
            $securityQuestionhelper = resolve('Lasallesoftware\Contactformfrontend\SecurityQuestionhelper');
            $question['first_number']  = $securityQuestionhelper->getRandomNumber();
            $question['second_number'] = $securityQuestionhelper->getRandomNumber();
        } else {
            $question = null;
        }

        return $question;
    }
}