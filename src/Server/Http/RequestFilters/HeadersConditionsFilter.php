<?php
namespace Mcustiel\Phiremock\Server\Http\RequestFilters;

use Mcustiel\SimpleRequest\Interfaces\FilterInterface;
use Mcustiel\SimpleRequest\Exception\FilterErrorException;

class HeadersConditionsFilter implements FilterInterface
{
    /**
     * @var ConvertToCondition
     */
    private $conditionFilter;

    public function __construct()
    {
        $this->conditionFilter = new ConvertToCondition();
    }

    public function filter($value)
    {
        if ($value === null) {
            return;
        }
        $this->checkValueIsArrayOrThrowException($value);

        $return = [];
        foreach ($value as $header => $condition) {
            $return[$header] = $this->conditionFilter->filter($condition);
        }
        return $return;
    }

    public function setSpecification($specification = null)
    {
        // TODO Auto-generated method stub
    }

    private function checkValueIsArrayOrThrowException($value)
    {
        if (!is_array($value)) {
            throw new FilterErrorException(
                'Error trying to parse headers condition. It should be a collection.'
                );
        }
    }
}
