<?php

namespace Fjord\Chart\Loader;

use Carbon\CarbonInterface;
use Closure;
use Fjord\Chart\ChartSet;

class DonutLoader extends ChartLoader
{
    use Concerns\HasOneIteration;

    /**
     * Make series.
     *
     * @param CarbonInterface $startTime
     * @param int             $iterations
     * @param Closure         $timeResolver
     * @param Closure         $valueResolver
     * @param Closure         $labelResolver
     *
     * @return array
     */
    protected function makeSeries(
        CarbonInterface $startTime,
        int $iterations,
        Closure $timeResolver,
        Closure $valueResolver,
        Closure $labelResolver
    ): array {
        $query = $this->config->model::query();

        $set = ChartSet::make($query, $valueResolver, $timeResolver)
            ->label($labelResolver)
            ->iterations(1);

        $set->load($startTime);

        return [
            'results' => [],
            'chart'   => $this->engine->render(
                $this->config->labels,
                $set
            ),
        ];
    }
}
