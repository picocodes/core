<?php

namespace MailOptin\Core\Repositories;


class OptinThemesRepository extends AbstractRepository
{
    private static $optin_themes;

    public static function defaultThemes()
    {
        if (is_null(self::$optin_themes)) {
            self::$optin_themes = apply_filters('mailoptin_registered_optin_themes', array(
                array(
                    'name' => 'BareMetal',
                    'optin_class' => 'BareMetal',
                    'optin_type' => 'lightbox', // accept comma delimited values eg lightbox,sidebar,inpost
                    'screenshot' => MAILOPTIN_ASSETS_URL . 'images/optin-themes/baremetal-lightbox.png'
                ),
                array(
                    'name' => 'Lupin',
                    'optin_class' => 'Lupin',
                    'optin_type' => 'sidebar', // accept comma delimited values eg lightbox,sidebar,inpost
                    'screenshot' => MAILOPTIN_ASSETS_URL . 'images/optin-themes/lupin-sidebar.png'
                ),
                array(
                    'name' => 'Columbine',
                    'optin_class' => 'Columbine',
                    'optin_type' => 'inpost', // accept comma delimited values eg lightbox,sidebar,inpost
                    'screenshot' => MAILOPTIN_ASSETS_URL . 'images/optin-themes/columbine-inpost.png'
                )
            ));
        }
    }

    /**
     * All Optin themes available.
     *
     * @return mixed
     */
    public static function get_all()
    {
        self::defaultThemes();
        return self::$optin_themes;
    }

    /**
     * Get optin themes of a given type.
     *
     * @param string $optin_type
     *
     * @return mixed
     */
    public static function get_by_type($optin_type)
    {
        $all = self::get_all();

        return array_reduce($all, function ($carry, $item) use ($optin_type) {

            // remove leading & trailing whitespace.
            $optin_type_array = array_map('trim', explode(',', $item['optin_type']));

            if (in_array($optin_type, $optin_type_array)) {
                $carry[] = $item;
            }

            return $carry;
        });
    }

    /**
     * Get optin theme by name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public static function get_by_name($name)
    {
        $all = self::get_all();

        return array_reduce($all, function ($carry, $item) use ($name) {

            if ($item['name'] == $name) {
                $carry = $item;
            }

            return $carry;
        });
    }

    /**
     * Add optin theme to theme repository.
     *
     * @param mixed $data
     *
     * @return void
     */
    public static function add($data)
    {
        self::defaultThemes();
        self::$optin_themes[] = $data;
    }

    /**
     * Delete optin theme from stack.
     *
     * @param mixed $optin_theme_name
     *
     * @return void
     */
    public static function delete_by_name($optin_theme_name)
    {
        self::defaultThemes();

        foreach (self::$optin_themes as $index => $optin_theme) {
            if ($optin_theme['name'] == $optin_theme_name) {
                unset(self::$optin_themes[$index]);
            }
        }
    }
}