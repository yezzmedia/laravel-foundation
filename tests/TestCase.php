<?php

declare(strict_types=1);

namespace Tests;

use YezzMedia\Foundation\Testing\Concerns\InteractsWithDoctorManager;
use YezzMedia\Foundation\Testing\Concerns\InteractsWithFeatureRegistry;
use YezzMedia\Foundation\Testing\Concerns\InteractsWithInstallManager;
use YezzMedia\Foundation\Testing\Concerns\InteractsWithPackageRegistry;
use YezzMedia\Foundation\Testing\FoundationTestCase;

abstract class TestCase extends FoundationTestCase
{
    use InteractsWithDoctorManager;
    use InteractsWithFeatureRegistry;
    use InteractsWithInstallManager;
    use InteractsWithPackageRegistry;
}
