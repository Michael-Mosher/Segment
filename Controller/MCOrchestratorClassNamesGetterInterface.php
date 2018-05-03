<?php

namespace Segment\Controller;

/**
 *
 * @author michaelmosher
 */
interface MCOrchestratorClassNamesGetterInterface extends \Segment\utilities\ForIterable
{
    public function getModelCallNames(string $x, bool $auth_required, string $model_call_name_csv): \ArrayAccess;
}
