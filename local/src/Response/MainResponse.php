<?php

namespace App\Response;

use App\Entity\Catalog\Application;
use App\Entity\Catalog\ProductSection;
use App\Entity\Content\Library;
use App\Entity\Content\News;
use App\Entity\Main\Banner;
use App\Entity\Main\Superiority;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;

class MainResponse extends BaseSuccessResponse
{
    public static $resultFields = [
        'banners',
        'news',
        'library',
        'sections',
        'applications',
        'superiority'
    ];
    protected ?array $banners = [];
    protected ?array $news = [];
    protected ?array $library = [];
    protected ?array $sections = [];
    protected ?array $applications = [];

    protected ?array $superiority = [];

    /**
     * @param array|null $banners
     */
    public function setBanners(?array $banners): void
    {
        $this->banners = $banners;

        $this->addCacheTag(Banner::class);
        $this->addCacheTagList($banners);
    }

    /**
     * @param array|null $news
     */
    public function setNews(?array $news): void
    {
        $this->news = $news;

        $this->addCacheTag(News::class);
        $this->addCacheTagList($news);
    }

    /**
     * @param array|null $library
     */
    public function setLibrary(?array $library): void
    {
        $this->library = $library;

        $this->addCacheTag(Library::class);
        $this->addCacheTagList($library);
    }

    /**
     * @param array|null $sections
     */
    public function setSections(?array $sections): void
    {
        $this->sections = $sections;

        $this->addCacheTag(ProductSection::class);
        $this->addCacheTagList($sections);
    }

    /**
     * @param array|null $applications
     */
    public function setApplications(?array $applications): void
    {
        $this->applications = $applications;

        $this->addCacheTag(Application::class);
        $this->addCacheTagList($applications);
    }

    /**
     * @param array|null $applications
     */
    public function setSuperiority(?array $superiority): void
    {
        $this->superiority = $superiority;

        $this->addCacheTag(Superiority::class);
        $this->addCacheTag($superiority['id']);
    }
}
