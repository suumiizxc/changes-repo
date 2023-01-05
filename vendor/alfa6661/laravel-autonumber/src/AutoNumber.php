<?php

namespace Alfa6661\AutoNumber;

use Alfa6661\AutoNumber\Models\AutoNumber as AutoNumberModel;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class AutoNumber
{
    /**
     * Generate unique name for autonumber identity.
     *
     * @param array $options
     * @return string
     */
    private function generateUniqueName(array $options)
    {
        //dd(md5(serialize($options)));
        return md5(serialize($options));
    }

    /**
     * Evaluate autonumber configuration.
     *
     * @param array $overrides
     * @return array
     */
    public function evaluateConfiguration(array $overrides = [])
    {
        $config = array_merge(
            app('config')->get('autonumber', []),
            $overrides
        );

        if (is_callable($config['format'])) {
            $config['format'] = call_user_func($config['format']);
        }

        foreach ($config as $key => $value) {
            if (is_null($value)) {
                throw new InvalidArgumentException($key.' param cannot be null');
            }
        }

        return $config;
    }

    /**
     * Return the next auto increment number.
     *
     * @param string $name
     * @return int
     */
    private function getNextNumber($name, $attr)
    {
        $autoNumber = AutoNumberModel::where('name', $name)->first();

        if ($autoNumber === null) {
            $autoNumber = new AutoNumberModel([
                'name' => $name,
                'number' => 1,
                'classes' => $attr['classes'],
                'format' => $attr['format'],
                'length' => $attr['length'],
            ]);
        } else {
            $autoNumber->number += 1;
        }

        $autoNumber->save();

        return $autoNumber->number;
    }

    /**
     * Generate auto number.
     *
     * @param Model $model
     * @return bool
     */
    public function generate(Model $model)
    {
        $attributes = [];
        foreach ($model->getAutoNumberOptions() as $attribute => $options) {
            if (is_numeric($attribute)) {
                $attribute = $options;
                $options = [];
            }

            $config = $this->evaluateConfiguration($options);

             $arr = array_merge(
                ['classes' => get_class($model)],
                array_except($config, ['onUpdate'])
            );
            
            $uniqueName = $this->generateUniqueName($arr);

            $autoNumber = $this->getNextNumber($uniqueName, $arr);

            if ($length = $config['length']) {
                $autoNumber = str_replace('?', str_pad($autoNumber, $length, '0', STR_PAD_LEFT), $config['format']);
            }

            $model->setAttribute($attribute, $autoNumber);

            $attributes[] = $attribute;
        }

        return $model->isDirty($attributes);
    }
}
