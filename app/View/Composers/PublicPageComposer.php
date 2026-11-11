<?php

namespace App\View\Composers;

use App\Queries\Public\ContentQuery;
use App\Queries\Settings\AuthenticationSettingsQuery;
use Illuminate\View\View;

class PublicPageComposer
{
    private ContentQuery $contents;

    private AuthenticationSettingsQuery $authentication;

    public function __construct(ContentQuery $contents, AuthenticationSettingsQuery $authentication)
    {
        $this->contents = $contents;
        $this->authentication = $authentication;
    }

    // ===== Compose =====

    public function compose(View $view): void
    {
        $view->with([
            'menuLinks'   => $this->menuLinks(),
            'publicLinks' => $this->publicLinks(),
            'footerLinks' => $this->footerLinks(),
            'contentMenu' => $this->contents->menu(),
        ]);
    }

    // ===== Helpers =====

    protected function menuLinks(): array
    {
        return [
            [
                'href'     => route('login'),
                'icon'     => 'fas fa-fw fa-sign-in-alt',
                'tile'     => 'bg-navy-soft text-info',
                'title'    => 'Login',
                'subtitle' => 'Officers and homeowners',
            ],
        ];
    }

    protected function publicLinks(): array
    {
        return [
            ['label' => 'Home',          'href' => route('home')],
            ['label' => 'Announcements', 'href' => route('announcements')],
            ['label' => 'HOA Officers',  'href' => route('members')],
            ['label' => 'Services',      'href' => route('services')],
            ['label' => 'Contact Us',    'href' => route('contact')],
        ];
    }

    protected function footerLinks(): array
    {
        $account = [['label' => 'Login', 'href' => route('login')]];

        if ($this->authentication->residentRegistrationAllowed()) {
            $account[] = ['label' => 'Register', 'href' => route('register')];
        }

        return [
            ['title' => 'Quick Links', 'items' => $this->publicLinks()],
            ['title' => 'Account', 'items' => $account],
        ];
    }
}
