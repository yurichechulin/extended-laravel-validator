<?php

namespace AvtoDev\ExtendedLaravelValidator;

use Illuminate\Contracts\Validation\Factory as Validator;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ExtendedValidatorServiceProvider extends IlluminateServiceProvider
{
    const SERVICE_PROVIDER_REGISTERED_ABSTRACT = 'extended-laravel-validator.registered';

    /**
     * Стек инстансов расширений валидатора.
     *
     * @var ValidationExtensionInterface[]
     */
    protected $extensions = [];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->instance(self::SERVICE_PROVIDER_REGISTERED_ABSTRACT, true);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootExtensions();

        $this->registerExtensions();
    }

    /**
     * Возвращает массив имен классов расширений валидатора, подлежащих загрузке.
     *
     * @return string[]
     */
    public function getExtensionsClassesNames()
    {
        return [
            Extensions\VinCodeValidatorExtension::class,
            Extensions\GrzCodeValidatorExtension::class,
            Extensions\StsCodeValidatorExtension::class,
            Extensions\PtsCodeValidatorExtension::class,
            Extensions\BodyCodeValidatorExtension::class,
            Extensions\ChassisCodeValidatorExtension::class,
            Extensions\DriverLicenseNumberValidatorExtension::class,
        ];
    }

    /**
     * Возвращает инстанс валидатора.
     *
     * @return Validator
     */
    protected function getValidator()
    {
        return $this->app->make('validator');
    }

    /**
     * Производит создание инстансов объектов-расширений валидатора.
     *
     * @see getExtensionsClassesNames
     *
     * @return void
     */
    protected function bootExtensions()
    {
        foreach ((array) $this->getExtensionsClassesNames() as $class_name) {
            $this->extensions[] = new $class_name;
        }
    }

    /**
     * Расширяет инстанс валидатора расширениями, что были инициализированы.
     *
     * @see bootExtensions
     *
     * @return void
     */
    protected function registerExtensions()
    {
        $validator = $this->getValidator();

        foreach ($this->extensions as &$extension) {
            $validator->extend($extension->name(), function ($attribute, $value) use (&$extension) {
                return (bool) $extension->passes($attribute, $value);
            }, $extension->message());
        }
    }
}
