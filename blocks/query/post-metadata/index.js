import ServerSideRender from '@wordpress/server-side-render';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { Notice, PanelBody } from '@wordpress/components';
import { createElement as el, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

function PostMetadataEdit() {
	const blockProps = useBlockProps( {
		className: 'pns-post-metadata-editor-preview',
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
					title: __( 'PNS Post Metadata', 'pns-blocks' ),
					initialOpen: true,
				},
				el(
					Notice,
					{
						status: 'info',
						isDismissible: false,
					},
					__(
						'Date and author update automatically from this post’s settings.',
						'pns-blocks'
					)
				)
			)
		),
		el(
			'div',
			blockProps,
			el( ServerSideRender, {
				block: 'pns/post-metadata',
			} )
		)
	);
}

registerBlockType( 'pns/post-metadata', {
	edit: PostMetadataEdit,
	save() {
		return null;
	},
} );
