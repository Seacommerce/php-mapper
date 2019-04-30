<?php


namespace Seacommerce\Mapper;


class MapperEvents
{
    /**
     * The pre_resolve event provides an opportunity to inspect the
     * source and target parameters that are passed into the mapper's
     * map($source, $target) method and allows you to provide the
     * class names that will be used to lookup the mapping in the registry.
     *
     * Providing a custom class name for either the source or the target
     * will bypass the default resolve mechanism and will use the class
     * name provided instead.
     */
    public const PRE_RESOLVE = 'mapper.pre_resolve';

    /**
     * The post_resolve event allows for replacing the class name for both
     * the source or the target after it was resolved by either the default
     * resolve mechanism or by any listeners to the pre_resolve event.
     */
    public const POST_RESOLVE = 'mapper.post_resolve';
}