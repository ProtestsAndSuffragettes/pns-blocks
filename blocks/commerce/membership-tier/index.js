import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { createElement as el } from '@wordpress/element';

import { createMembershipTierTemplate } from './template';
import './editor.css';
import './style.css';

const membershipTierTemplate = createMembershipTierTemplate();

function MembershipTierEdit() {
	const blockProps = useBlockProps( {
		className: 'pns-membership-tier',
	} );

	return el(
		'div',
		blockProps,
		el( InnerBlocks, {
			template: membershipTierTemplate,
			templateLock: 'contentOnly',
		} )
	);
}

registerBlockType( 'pns/membership-tier', {
	icon: 'index-card',
	edit: MembershipTierEdit,
	save() {
		return el(
			'div',
			useBlockProps.save( {
				className: 'pns-membership-tier',
			} ),
			el( InnerBlocks.Content )
		);
	},
} );
