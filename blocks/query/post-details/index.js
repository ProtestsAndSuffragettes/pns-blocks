import ServerSideRender from '@wordpress/server-side-render';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { Notice, PanelBody } from '@wordpress/components';
import { createElement as el, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

function PostDetailsEdit() {
	const blockProps = useBlockProps( {
		className: 'pns-post-details-editor-preview',
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
					title: __( 'PNS Post Details', 'pns-blocks' ),
					initialOpen: true,
				},
				el(
					Notice,
					{
						status: 'info',
						isDismissible: false,
					},
					__(
						'Date, author, categories, and tags update automatically from this post’s settings.',
						'pns-blocks'
					)
				)
			)
		),
		el(
			'div',
			blockProps,
			el( ServerSideRender, {
				block: 'pns/post-details',
			} )
		)
	);
}

registerBlockType( 'pns/post-details', {
	edit: PostDetailsEdit,
	save() {
		return null;
	},
} );
