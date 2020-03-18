<?php
/**
 * Class TheMovieDb
 *
 * @author Furkan Sezgin (ZquaRe)
 * @mail furkan-sezgin@hotmail.com
 */

/*
WARNING:
It pulls data from imdb.com, this process will slow its the system operation.
*/

class TheMovieDb
{
    private $url = null;
    private $folder = null;
    private $name = null;
    private $API_KEY = null;
    const    API_URL = 'https://api.themoviedb.org/3/';
    const    IMDB_URL = 'https://www.imdb.com/title/';

    /**
     * TheMovieDb constructor.
     * @param $API_KEY
     */
    public function __construct($API_KEY)
    {
        $this->API_KEY = $API_KEY;

        if (empty($this->API_KEY)) {
            echo
            json_encode(
                array(
                    'status' => 'error',
                    'description' => 'API Key Not Found',
                    'class' => get_class($this),
                    'function' => __FUNCTION__)
            );
        }
    }

    /**
     * @param $API_KEY
     */
    public function settings($API_KEY)
    {
        $this->API_KEY = $API_KEY;
        if (empty($this->API_KEY)) {
            echo
            json_encode(
                array(
                    'status' => 'error',
                    'description' => 'API Key Not Found',
                    'class' => get_class($this),
                    'function' => __FUNCTION__)
            );
        }

    }

    /**
     * @param $url
     * @return bool
     */
    private function remoteFileCheck($url)
    {
        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Check the response code
        if ($responseCode == 200) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $url
     */
    private function cUrl($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; tr; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6');
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        return $result;
        curl_close($ch);
    }

    /**
     * @param $Request
     * @param null $Imdb
     * @return mixed
     */
    private function request($Request, $Imdb = null)
    {
        if (empty($Imdb))
            return $this->cUrl(self::API_URL . $Request);
        else if (!empty($Imdb))
            return $this->cUrl(self::IMDB_URL . $Request);
    }

    /**
     * @param $movieName
     * @param string $language
     * @param int $page
     * @return mixed
     */
    public function search($movieName, $language = 'tr', $page = 1)
    {
        $this->movieName = $movieName;
        $this->movieRawName = rawurlencode($movieName);
        $this->language = $language;
        $this->page = $page;


        $MovResult = json_decode($this->request('search/multi?api_key=' . $this->API_KEY . '&language=' . $this->language . '&query=' . $this->movieRawName . '&page=' . $this->page . '&include_adult=false'));

        $this->Mov_id = $MovResult->results[0]->id;

        if (!empty($MovResult->results[0]->title))
            $this->Mov_title = $MovResult->results[0]->title;
        else
            $this->Mov_title = null;

        if (!empty($MovResult->results[0]->name))
            $this->Mov_name = $MovResult->results[0]->name;
        else if (!empty($MovResult->results[0]->original_title))
            $this->Mov_name = $MovResult->results[0]->original_title;

        $this->Mov_type = $MovResult->results[0]->media_type;

        if (!empty($MovResult->results[0]->first_air_date))
            $this->Mov_date = $MovResult->results[0]->first_air_date;
        else if (!empty($MovResult->results[0]->release_date))
            $this->Mov_date = $MovResult->results[0]->release_date;

        $this->Mov_overview = $MovResult->results[0]->overview;
        $this->Mov_poster_path = $MovResult->results[0]->poster_path;
        $this->Mov_images = 'https://image.tmdb.org/t/p/original' . $MovResult->results[0]->poster_path;

        return $MovResult;


    }

    /**
     * @return mixed
     */
    public function imdb_id()
    {
        return $Mov_imdbid = json_decode($this->request($this->Mov_type . '/' . $this->Mov_id . '/external_ids?api_key=' . $this->API_KEY))->imdb_id;
    }

    /**
     * @return mixed
     */
    public function imdb_ranked()
    {
        $this->imdbid = self::imdb_id();

        preg_match_all('#<span class="small" itemprop="ratingCount">(.*)</span>#', self::request($this->imdbid . '/', true), $RatingCount);
        preg_match_all('#<span itemprop="ratingValue">(.*)</span>#', self::request($this->imdbid . '/', true), $Result);
        $this->imdb_rating = explode('/', strip_Tags($Result[0][0]))[0];
        $this->imdb_bestRating = explode('/', strip_Tags($Result[0][0]))[1];
        $this->imdb_ratingCount = $RatingCount[1][0];
        return json_decode(json_encode(array('Imdbid' => $this->imdbid, 'Rating' => $this->imdb_rating, 'BestRating' => $this->imdb_bestRating, 'RatingTotalUserCount' => $this->imdb_ratingCount)));
    }

    /**
     * @return mixed
     */
    public function cast()
    {
        //  https://api.themoviedb.org/3/tv/{tv_id}/credits?api_key={API_KEY}&language=tr
        return $MovResult = json_decode($this->request($this->Mov_type . '/' . $this->Mov_id . '/credits?api_key=' . $this->API_KEY . '&language=tr'))->cast;
    }

    /**
     * @return mixed
     */
    public function recommendations()
    {
        //  https://api.themoviedb.org/3/movie/{movie_id}/recommendations?api_key={API_KEY}&language=tr&page=1
        return $MovResult = json_decode($this->request($this->Mov_type . '/' . $this->Mov_id . '/recommendations?api_key=' . $this->API_KEY . '&language=tr&page=1'))->results;
    }

    /**
     * @return null
     */
    public function seasons()
    {
        if ($this->Mov_type == 'tv') {
            //https://api.themoviedb.org/3/tv/1396?api_key={API_KEY}&language=tr
            return $MovResult = json_decode($this->request('tv/' . $this->Mov_id . '?api_key=' . $this->API_KEY . '&language=tr'))->seasons;
        } else {
            return null;
        }
    }


    /**
     * @return mixed
     */
    public function all()
    {


        foreach (self::cast() as $cast) {

            $this->Cast[] = array(
                'Cast_id' => $cast->id,
                'Character' => $cast->character,
                'Cast_name' => $cast->name,
                'Cast_profilepath' => $cast->profile_path,
                'Cast_profilepic' => 'https://image.tmdb.org/t/p/original' . $cast->profile_path
            );
        }


        foreach (self::recommendations() as $recommendations) {
            if (!empty($recommendations->original_title)) $recommendations->original_title = $recommendations->original_title; else $recommendations->original_title = $recommendations->name;
            if (!empty($recommendations->release_date)) $recommendations->release_date = $recommendations->release_date; else $recommendations->release_date = null;

            $this->Recommendations[] = array(
                'Movie_id' => $recommendations->id,
                'Mov_title' => $recommendations->original_title,
                'Mov_overview' => $recommendations->overview,
                'Mov_release_date' => $recommendations->release_date,
                'Mov_poster_path' => $recommendations->poster_path,
                'Mov_pic' => 'https://image.tmdb.org/t/p/original' . $recommendations->poster_path
            );
        }

        if ($this->Mov_type == 'tv') {
            foreach (self::seasons() as $seasons) {

                $this->Seasons[] = array(
                    'release_date' => $seasons->air_date,
                    'episode_count' => $seasons->episode_count,
                    'season_id' => $seasons->id,
                    'overview' => $seasons->overview,
                    'poster_path' => $seasons->poster_path,
                    'poster_image' => 'https://image.tmdb.org/t/p/original' . $seasons->poster_path,
                    'season_number' => $seasons->season_number,

                );
            }
        } else {
            $this->Seasons = null;
        }


        return json_decode(json_encode(
            array(
                'Mov_id' => $this->Mov_id,
                'Mov_name' => $this->Mov_name,
                'Mov_title' => $this->Mov_title,
                'Mov_type' => $this->Mov_type,
                'Mov_date' => $this->Mov_date,
                'Mov_overview' => $this->Mov_overview,
                'Mov_poster_path' => $this->Mov_poster_path,
                'Mov_images' => $this->Mov_images,
                'imdb' => $this->imdb_ranked(),
                'Seasons' => $this->Seasons,
                'Cast' => $this->Cast,
                'Recommendations' => $this->Recommendations

            )
        ));
    }

    /**
     * @param $url
     * @param $folder
     * @param null $name
     * @return string
     */
    public function fileDownload($url, $folder, $name = null)
    {
        $this->url = $url;
        $this->folder = $folder;
        $this->name = $name;

        if (self::remoteFileCheck($this->url)) {
            $this->imageName = explode("/", $this->url);
            $this->imageName = end($this->imageName);
            $this->extension = pathinfo($this->imageName)['extension'];

            if (!empty($this->folder)) {
                if (file_exists($this->folder)) {
                    if (!empty($this->name)) {
                        copy($this->url, $this->folder . '/' . $this->name . '.' . $this->extension);
                    } else {
                        copy($this->url, $this->folder . '/' . $this->imageName);
                    }
                } else {
                    return json_encode(
                        array(
                            'status' => 'error',
                            'description' => 'Folder not found',
                            'folder' => $this->folder,
                            'class' => get_class($this),
                            'function' => __FUNCTION__)
                    );
                }
            } else {
                if (!empty($this->name)) {
                    copy($this->url, $this->name . '.' . $this->extension);
                } else {
                    copy($this->url, $this->imageName);
                }
            }
        } else {
            return json_encode(
                array(
                    'status' => 'error',
                    'description' => 'File not available on remote server',
                    'Url' => $this->url,
                    'class' => get_class($this),
                    'function' => __FUNCTION__
                )
            );
        }
    }
}
?>