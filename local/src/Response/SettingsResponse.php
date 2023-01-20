<?php

namespace App\Response;

use App\Entity\Content\Library;
use App\Entity\Content\News;
use App\Entity\Main\Banner;
use App\Entity\Settings\Contacts;
use App\Entity\Settings\Menu;
use App\Entity\Settings\Office;
use App\Entity\Settings\Settings;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;

class SettingsResponse extends BaseSuccessResponse
{
    public static $resultFields = [
        'logo',
        'menu',
        'rules',
        'offices',
        'contacts',
        'politic',
    ];
    protected ?array $menu = [];
    protected ?string $logo = '';
    protected ?string $rules = '';
    protected ?array $offices = [];
    protected ?array $contacts = [];
    protected ?string $politic = '';

    /**
     * @param array|null $menu
     */
    public function setMenu(?array $menu): void
    {
        $this->menu = $menu;

        $this->addCacheTag(Menu::class);
        $this->addCacheTagList($menu);
    }

    /**
     * @param string|null $rules
     */
    public function setRules(?string $rules): void
    {
        $this->rules = $rules;

        $this->addCacheTag(Settings::class);
    }

    /**
     * @param array|null $offices
     */
    public function setOffices(?array $offices): void
    {
        $this->offices = $offices;

        $this->addCacheTag(Office::class);
        $this->addCacheTagList($offices);
    }

    /**
     * @param array|null $contacts
     */
    public function setContacts(?array $contacts): void
    {
        $this->contacts = $contacts;

        $this->addCacheTag(Contacts::class);
        $this->addCacheTagList($contacts);
    }

    /**
     * @param string|null $politic
     */
    public function setPolitic(?string $politic): void
    {
        $this->politic = $politic;

        $this->addCacheTag(Settings::class);
    }

    /**
     * @param string|null $logo
     */
    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;

        $this->addCacheTag(Settings::class);
    }
}
