<?php
/**
 * @SWG\Resource(
 *  basePath="/foo",
 *  description="Foo operations",
 *  produces="['application/json','application/xml']"
 * )
 */
class FooController
{
    /**
     * @SWG\Api(
     *   path="/getBar.{_format}",
     *   @SWG\Operation(
     *      method="GET",
     *      summary="Make bar",
     *      nickname="getBar",
     *      type="Foo"
     *   )
     * )
     */
}
