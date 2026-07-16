import ServerSideRender from '@wordpress/server-side-render';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import {
	PanelBody,
	RangeControl,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { createElement as el, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const POST_TYPE_OPTIONS = [
	{ label: __( 'News posts', 'pns-blocks' ), value: 'post' },
	{ label: __( 'Herstories', 'pns-blocks' ), value: 'herstory' },
	{ label: __( 'Pages', 'pns-blocks' ), value: 'page' },
];

const ORDER_OPTIONS = [
	{ label: __( 'Newest first', 'pns-blocks' ), value: 'desc' },
	{ label: __( 'Oldest first', 'pns-blocks' ), value: 'asc' },
];

const ORDER_BY_OPTIONS = [
	{ label: __( 'Publication date', 'pns-blocks' ), value: 'date' },
	{ label: __( 'Menu order', 'pns-blocks' ), value: 'menu_order' },
	{ label: __( 'Title', 'pns-blocks' ), value: 'title' },
];

const LAYOUT_OPTIONS = [
	{
		label: __( 'Edge media left', 'pns-blocks' ),
		value: 'edge-media-left',
	},
	{
		label: __( 'Edge media right', 'pns-blocks' ),
		value: 'edge-media-right',
	},
	{ label: __( 'Media left', 'pns-blocks' ), value: 'media-left' },
	{ label: __( 'Media right', 'pns-blocks' ), value: 'media-right' },
];

function FeaturedPostEdit( props ) {
	const { attributes, setAttributes } = props;
	const blockProps = useBlockProps( {
		className: 'pns-featured-post-editor-preview',
	} );

	return el(
		Fragment,
		null,
		el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{
					title: __( 'Featured post', 'pns-blocks' ),
					initialOpen: true,
				},
				el( SelectControl, {
					label: __( 'Post type', 'pns-blocks' ),
					value: attributes.postType,
					options: POST_TYPE_OPTIONS,
					onChange( postType ) {
						const defaults =
							postType === 'herstory'
								? {
										heading: __(
											'Featured Herstory:',
											'pns-blocks'
										),
										moreText: __(
											'Read this herstory',
											'pns-blocks'
										),
										order: 'asc',
										orderBy: 'menu_order',
								  }
								: {
										heading: __(
											'Featured News:',
											'pns-blocks'
										),
										moreText: __(
											'Read latest news',
											'pns-blocks'
										),
										order: 'desc',
										orderBy: 'date',
								  };

						setAttributes( {
							postType,
							...defaults,
						} );
					},
				} ),
				el( TextControl, {
					label: __( 'Heading', 'pns-blocks' ),
					value: attributes.heading,
					onChange( heading ) {
						setAttributes( { heading } );
					},
				} ),
				el( TextControl, {
					label: __( 'Excerpt link text', 'pns-blocks' ),
					value: attributes.moreText,
					onChange( moreText ) {
						setAttributes( { moreText } );
					},
				} ),
				el( SelectControl, {
					label: __( 'Order by', 'pns-blocks' ),
					value: attributes.orderBy,
					options: ORDER_BY_OPTIONS,
					onChange( orderBy ) {
						setAttributes( { orderBy } );
					},
				} ),
				el( SelectControl, {
					label: __( 'Order', 'pns-blocks' ),
					value: attributes.order,
					options: ORDER_OPTIONS,
					onChange( order ) {
						setAttributes( { order } );
					},
				} ),
				el( RangeControl, {
					label: __( 'Offset', 'pns-blocks' ),
					min: 0,
					max: 20,
					value: attributes.offset,
					onChange( offset ) {
						setAttributes( { offset: offset || 0 } );
					},
				} ),
				el( SelectControl, {
					label: __( 'Layout', 'pns-blocks' ),
					value: attributes.layoutVariant,
					options: LAYOUT_OPTIONS,
					onChange( layoutVariant ) {
						setAttributes( { layoutVariant } );
					},
				} )
			)
		),
		el(
			'div',
			blockProps,
			el( ServerSideRender, {
				block: 'pns/featured-post',
				attributes,
			} )
		)
	);
}

registerBlockType( 'pns/featured-post', {
	edit: FeaturedPostEdit,
	save() {
		return null;
	},
} );
