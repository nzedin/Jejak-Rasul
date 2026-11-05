<?php

namespace App\Exceptions;

use DomainException;

/** Dilempar apabila tindakan melanggar aliran semakan kandungan. */
class WorkflowException extends DomainException {}
