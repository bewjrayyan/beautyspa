<?php

namespace Modules\Admin\Ui;

use InvalidArgumentException;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ViewErrorBag;

abstract class Tabs
{
    /**
     * Array of all groups.
     *
     * @var array
     */
    protected $groups = [];

    /**
     * Current group name of the tabs.
     *
     * @var string
     */
    protected $group;

    /**
     * Array of all tabs.
     *
     * @var array
     */
    protected $tabs = [];

    /**
     * Indicate that submit button should add offset class.
     *
     * @var bool
     */
    protected $buttonOffset = true;


    /**
     * Make new tabs with groups.
     *
     * @return void
     */
    abstract public function make();


    /**
     * Set current group as active group.
     *
     * @return self
     */
    public function active()
    {
        $this->groups[$this->group]['active'] = true;

        return $this;
    }


    /**
     * Add new tab.
     *
     * @param Tab|null $tab
     *
     * @return void
     */
    public function add($tab)
    {
        if (!is_null($tab)) {
            $this->tabs[$this->group][] = $tab;
        }

        return $this;
    }


    /**
     * Determine if tabs fields has any error.
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->getErrors()->hasAny($this->getTabFields());
    }


    /**
     * Generate navs for the tabs.
     *
     * @param array $data
     *
     * @return HtmlString
     */
    public function navs($data = [])
    {
        return new HtmlString($this->getTabsData('nav', $data));
    }


    /**
     * Render the tabs,
     *
     * @param array $data
     *
     * @return void
     */
    public function render($data = [])
    {
        $this->activateTabFromRequest();

        return view('admin::components.accordion', [
            'tabs' => $this,
            'name' => class_basename($this),
            'groups' => $this->groups(),
            'contents' => $this->contents($data),
            'buttonOffset' => $this->buttonOffset,
        ]);
    }


    /**
     * Render all tabs on one page (no sidebar navigation).
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function renderStacked($data = [])
    {
        return view('admin::components.stacked-form', [
            'contents' => $this->stackedContents($data),
            'buttonOffset' => $this->buttonOffset,
        ]);
    }


    /**
     * Modern settings page layout (sidebar navigation + content panel).
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function renderSettings($data = [])
    {
        $this->activateTabFromRequest();

        return view('admin::components.settings-layout', [
            'navigation' => $this->settingsNavigation(),
            'contents' => $this->contents($data),
            'buttonOffset' => $this->buttonOffset,
            'activeTab' => $this->activeTabName(),
            'activeTabMeta' => $this->activeTabMeta(),
        ]);
    }


    /**
     * @return array{name: string, label: string, group: string, lead: ?string, icon: ?string}
     */
    protected function activeTabMeta(): array
    {
        foreach ($this->groups as $groupName => $options) {
            foreach ($this->tabs[$groupName] ?? [] as $tab) {
                if (! $tab->active) {
                    continue;
                }

                $leadKey = "setting::settings.tab_leads.{$tab->name}";
                $lead = trans($leadKey);

                return [
                    'name' => $tab->name,
                    'label' => $tab->label,
                    'group' => $options['title'] ?? $groupName,
                    'lead' => $lead === $leadKey ? null : $lead,
                    'icon' => $tab->settingsNavIcon(),
                ];
            }
        }

        return [
            'name' => 'general',
            'label' => '',
            'group' => '',
            'lead' => null,
            'icon' => null,
        ];
    }


    protected function activateTabFromRequest(): void
    {
        if (! request()->filled('tab')) {
            return;
        }

        $tabName = request()->query('tab');

        if (! $this->collect()->pluck('*.name')->flatten()->contains($tabName)) {
            return;
        }

        foreach ($this->groups as $groupName => $group) {
            $this->groups[$groupName]['active'] = false;
        }

        foreach ($this->tabs as $groupName => $group) {
            foreach ($group as $tab) {
                if ($tab->name === $tabName) {
                    $tab->active = true;
                    $this->groups[$groupName]['active'] = true;
                } else {
                    $tab->active = false;
                }
            }
        }
    }


    protected function activeTabName(): ?string
    {
        foreach ($this->tabs as $group) {
            foreach ($group as $tab) {
                if ($tab->active) {
                    return $tab->name;
                }
            }
        }

        return null;
    }


    protected function settingsNavigation(): string
    {
        $html = '';
        $baseUrl = route('admin.settings.edit');

        foreach ($this->groups as $groupName => $options) {
            $title = $options['title'] ?? $groupName;

            $html .= '<div class="settings-nav-group is-expanded" data-settings-group>';
            $html .= '<button type="button" class="settings-nav-group__toggle" aria-expanded="true">';
            $html .= '<span class="settings-nav-group__title">'.e($title).'</span>';
            $html .= '<i class="fa fa-chevron-down settings-nav-group__chevron" aria-hidden="true"></i>';
            $html .= '</button>';
            $html .= '<ul class="settings-nav settings-nav-group__list">';

            foreach ($this->group($groupName)->getSortedTabs() as $tab) {
                $html .= $tab->getSettingsNav($baseUrl);
            }

            $html .= '</ul></div>';
        }

        return $html;
    }


    /**
     * @param array $data
     *
     * @return HtmlString
     */
    protected function stackedContents($data = [])
    {
        $contents = '';

        foreach ($this->groups as $group => $options) {
            foreach ($this->group($group)->getSortedTabs() as $tab) {
                $contents .= $tab->getStackedView($data);
            }
        }

        return new HtmlString($contents);
    }


    /**
     * Set group name.
     *
     * @param string $name
     * @param string $title
     *
     * @return self
     */
    public function group($name, $title = null)
    {
        $this->group = $name;

        if (!is_null($title)) {
            $this->groups[$name]['title'] = $title;
        }

        return $this;
    }


    /**
     * Get error message bag.
     *
     * @return ViewErrorBag
     */
    protected function getErrors()
    {
        return request()->session()->get('errors') ?: new ViewErrorBag;
    }


    /**
     * Get all tabs fields.
     *
     * @return array
     */
    protected function getTabFields()
    {
        return array_reduce($this->getSortedTabs(), function ($fields, Tab $tab) {
            return array_merge($fields, $tab->getFields());
        }, []);
    }


    /**
     * Get validation/save fields registered for a settings tab.
     */
    public function fieldsForTab(string $tabName): array
    {
        foreach ($this->tabs as $group) {
            foreach ($group as $tab) {
                if ($tab->name === $tabName) {
                    return $tab->getFields();
                }
            }
        }

        return [];
    }


    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function tabNames()
    {
        return $this->collect()->pluck('*.name')->flatten();
    }


    /**
     * Get sorted tabs.
     *
     * @return array
     */
    protected function getSortedTabs()
    {
        return collect($this->tabs[$this->group])->sortBy(function (Tab $tab) {
            return $tab->getWeight();
        })->all();
    }


    /**
     * Get tabs data for the given type.
     *
     * @param string $type
     * @param array $data
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function getTabsData($type, $data = [])
    {
        if (!array_key_exists($this->group, $this->tabs)) {
            throw new InvalidArgumentException("Group [$this->group] is not registered.");
        }

        $html = '';

        foreach ($this->getSortedTabs() as $tab) {
            $method = 'get' . ucfirst($type);

            if (method_exists($tab, $method)) {
                $html .= call_user_func_array([$tab, $method], [$data]);
            }
        }

        return $html;
    }


    /**
     * Get all groups with its options.
     *
     * @return array
     */
    protected function groups()
    {
        $groups = [];

        foreach ($this->groups as $group => $options) {
            $groups[$group] = $options;
        }

        return $groups;
    }


    /**
     * Generate contents for the tabs.
     *
     * @param array $data
     *
     * @return HtmlString
     */
    protected function contents($data = [])
    {
        $contents = '';

        foreach ($this->groups as $group => $options) {
            $contents .= $this->group($group)->getTabsData('view', $data);
        }

        return new HtmlString($contents);
    }


    public function toArray()
    {
        $arrayedTabs = [];
        foreach ($this->tabs as $group => $tabs) {
            foreach ($tabs as $tab) {
                $arrayedTabs[$group][$tab->name] = $tab->toArray();
            }
        }

        return $arrayedTabs;
    }


    public function collect()
    {
        return collect($this->toArray());
    }
}
