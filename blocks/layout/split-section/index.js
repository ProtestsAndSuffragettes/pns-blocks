import {
	InspectorControls,
	InnerBlocks,
	useBlockProps,
} from "@wordpress/block-editor";
import { createBlock, registerBlockType } from "@wordpress/blocks";
import {
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOptionIcon as ToggleGroupControlOptionIcon,
	PanelBody,
	SelectControl,
} from "@wordpress/components";
import { useDispatch, useSelect } from "@wordpress/data";
import { createElement as el, Fragment } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import "./editor.css";
import "./style.css";

const defaultLayoutVariant = "media-right";
const layoutOptions = [
	{
		label: __("Media left", "pns-blocks"),
		value: "media-left",
	},
	{
		label: __("Media right", "pns-blocks"),
		value: "media-right",
	},
	{
		label: __("Edge media left", "pns-blocks"),
		value: "edge-media-left",
	},
	{
		label: __("Edge media right", "pns-blocks"),
		value: "edge-media-right",
	},
];
const allowedLayoutVariants = layoutOptions.map(function (option) {
	return option.value;
});
const defaultMediaType = "image";
const mediaOptions = [
	{
		label: __("Image", "pns-blocks"),
		value: "image",
	},
	{
		label: __("Jetpack slideshow", "pns-blocks"),
		value: "slideshow",
	},
	{
		label: __("Video file", "pns-blocks"),
		value: "video",
	},
];
const allowedMediaTypes = mediaOptions.map(function (option) {
	return option.value;
});
const SPLIT_SECTION_ICON = el(
	"svg",
	{
		"aria-hidden": "true",
		focusable: "false",
		viewBox: "0 0 24 24",
		xmlns: "http://www.w3.org/2000/svg",
	},
	el("path", {
		d: "M4 5h7v14H4V5Zm9 0h7v14h-7V5Z",
		fill: "currentColor",
	}),
);
const SPLIT_IMAGE_ICON = el(
	"svg",
	{
		"aria-hidden": "true",
		focusable: "false",
		viewBox: "0 0 24 24",
		xmlns: "http://www.w3.org/2000/svg",
	},
	el("path", {
		d: "M4 5h7v14H4V5Zm9 0h7v14h-7V5Zm1.5 10.5h4l-1.15-1.55-.9 1.1-1.05-1.45-.9 1.9Z",
		fill: "currentColor",
	}),
	el("circle", {
		cx: "16.5",
		cy: "9",
		fill: "#fff",
		r: "1",
	}),
);
const SPLIT_VIDEO_ICON = el(
	"svg",
	{
		"aria-hidden": "true",
		focusable: "false",
		viewBox: "0 0 24 24",
		xmlns: "http://www.w3.org/2000/svg",
	},
	el("path", {
		d: "M4 5h7v14H4V5Zm9 0h7v14h-7V5Zm2.5 4v6l4-3-4-3Z",
		fill: "currentColor",
	}),
);
const SPLIT_SLIDESHOW_ICON = el(
	"svg",
	{
		"aria-hidden": "true",
		focusable: "false",
		viewBox: "0 0 24 24",
		xmlns: "http://www.w3.org/2000/svg",
	},
	el("path", {
		d: "M4 5h7v14H4V5Zm9 0h7v14h-7V5Zm1.5 3h3.5v5.5h-3.5V8Zm1.5 7h3.5v1.5H16V15Z",
		fill: "currentColor",
	}),
);

const copyTemplate = [
	[
		"core/heading",
		{
			content: __("Section Heading", "pns-blocks"),
		},
	],
	[
		"core/paragraph",
		{
			content: __(
				"Use this split section for focused copy with related media. Replace this starter text before publishing.",
				"pns-blocks",
			),
		},
	],
	[
		"core/paragraph",
		{
			className: "pns-split-section__cta",
			content:
				'<a class="wp-block-button__link wp-element-button" href="#">' +
				__("Find out more", "pns-blocks") +
				"</a>",
		},
	],
];
const imageMediaTemplate = [
	[
		"core/image",
		{
			id: 1122,
			sizeSlug: "large",
			linkDestination: "none",
			url: "/wp-content/uploads/2022/08/Our_work_with_Wikipedia_Image@2x-1024x912.jpeg",
		},
	],
];

const videoMediaTemplate = [["core/video", {}]];

const slideshowMediaTemplate = [
	[
		"jetpack/slideshow",
		{
			ids: [2077, 2098, 2091, 2327, 2074, 2350, 1122],
			sizeSlug: "square",
		},
	],
];

function splitSectionTemplate(mediaTemplate, copyText, mediaColumnClassName) {
	return [
		[
			"core/columns",
			{
				align: "full",
				className: "pns-split-section__columns",
			},
			[
				[
					"core/column",
					{
						className: "pns-split-section__copy-column",
					},
					[
						[
							"core/group",
							{
								className: "pns-split-section__copy",
							},
							copyTemplate.map(function (block) {
								if (
									block[0] !== "core/paragraph" ||
									block[1].className ||
									!copyText
								) {
									return block;
								}

								return [
									block[0],
									{
										...block[1],
										content: copyText,
									},
								];
							}),
						],
					],
				],
				[
					"core/column",
					{
						className:
							mediaColumnClassName ||
							"pns-split-section__media-column",
					},
					mediaTemplate,
				],
			],
		],
	];
}

const imageTemplate = splitSectionTemplate(
	imageMediaTemplate,
	__(
		"Use this split section for focused copy with a related image. Replace this starter text before publishing.",
		"pns-blocks",
	),
);
const videoTemplate = splitSectionTemplate(
	videoMediaTemplate,
	__(
		"Use this split section for focused copy with a related video. Replace this starter text before publishing.",
		"pns-blocks",
	),
	"pns-split-section__media-column pns-split-section__media-column--video",
);
const slideshowTemplate = splitSectionTemplate(
	slideshowMediaTemplate,
	__(
		"Use this split section for focused copy with a related image slideshow. Replace this starter text before publishing.",
		"pns-blocks",
	),
);

function normaliseLayoutVariant(layoutVariant) {
	return allowedLayoutVariants.includes(layoutVariant)
		? layoutVariant
		: defaultLayoutVariant;
}

function normaliseMediaType(mediaType) {
	return allowedMediaTypes.includes(mediaType) ? mediaType : defaultMediaType;
}

function getMediaColumn(block) {
	const columns = block?.innerBlocks?.find(function (innerBlock) {
		return innerBlock.name === "core/columns";
	});

	return columns?.innerBlocks?.find(function (innerBlock) {
		return innerBlock.attributes?.className?.includes(
			"pns-split-section__media-column",
		);
	});
}

function getMediaTypeFromColumn(mediaColumn) {
	const mediaBlockName = mediaColumn?.innerBlocks?.[0]?.name;

	if (mediaBlockName === "jetpack/slideshow") {
		return "slideshow";
	}

	if (mediaBlockName === "core/video" || mediaBlockName === "core/embed") {
		return "video";
	}

	return defaultMediaType;
}

function getMediaColumnClassName(mediaType) {
	return [
		"pns-split-section__media-column",
		...(mediaType === "video"
			? ["pns-split-section__media-column--video"]
			: []),
	].join(" ");
}

function createMediaBlock(mediaType) {
	const blockName = {
		image: "core/image",
		slideshow: "jetpack/slideshow",
		video: "core/video",
	}[normaliseMediaType(mediaType)];

	return createBlock(blockName);
}

function getSplitSectionClassName(attributes) {
	const layoutVariant = normaliseLayoutVariant(attributes.layoutVariant);

	return [
		"pns-section",
		"pns-layout",
		"pns-split-section",
		"pns-site-frame-panel",
		"is-style-pns-" + layoutVariant,
	].join(" ");
}

function SplitSectionEdit(props) {
	const attributes = props.attributes;
	const mediaColumn = useSelect(
		function (select) {
			return getMediaColumn(
				select("core/block-editor").getBlock(props.clientId),
			);
		},
		[props.clientId],
	);
	const { replaceInnerBlocks, updateBlockAttributes } =
		useDispatch("core/block-editor");
	const mediaType = normaliseMediaType(
		attributes.mediaType || getMediaTypeFromColumn(mediaColumn),
	);
	const blockProps = useBlockProps({
		className: getSplitSectionClassName(attributes),
	});
	function changeMediaType(nextMediaType) {
		const normalisedMediaType = normaliseMediaType(nextMediaType);

		props.setAttributes({
			mediaType: normalisedMediaType,
		});

		if (!mediaColumn || normalisedMediaType === mediaType) {
			return;
		}

		updateBlockAttributes(mediaColumn.clientId, {
			className: getMediaColumnClassName(normalisedMediaType),
		});
		replaceInnerBlocks(
			mediaColumn.clientId,
			[createMediaBlock(normalisedMediaType)],
			true,
		);
	}

	return el(
		Fragment,
		null,
		el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{
					title: __("Split section", "pns-blocks"),
					initialOpen: true,
				},
				el(
					ToggleGroupControl,
					{
						label: __("Media type", "pns-blocks"),
						hideLabelFromVision: true,
						help: __(
							"Changing this replaces the current media only. Add the new image, slideshow, or video file afterward.",
							"pns-blocks",
						),
						value: mediaType,
						onChange: changeMediaType,
						__next40pxDefaultSize: true,
					},
					el(ToggleGroupControlOptionIcon, {
						value: "image",
						label: __("Image", "pns-blocks"),
						icon: SPLIT_IMAGE_ICON,
					}),
					el(ToggleGroupControlOptionIcon, {
						value: "video",
						label: __("Video file", "pns-blocks"),
						icon: SPLIT_VIDEO_ICON,
					}),
					el(ToggleGroupControlOptionIcon, {
						value: "slideshow",
						label: __("Jetpack slideshow", "pns-blocks"),
						icon: SPLIT_SLIDESHOW_ICON,
					}),
				),
				el(SelectControl, {
					label: __("Layout", "pns-blocks"),
					value: normaliseLayoutVariant(attributes.layoutVariant),
					options: layoutOptions,
					onChange(layoutVariant) {
						props.setAttributes({
							layoutVariant:
								normaliseLayoutVariant(layoutVariant),
						});
					},
				}),
			),
		),
		el(
			"div",
			blockProps,
			el(InnerBlocks, {
				template: imageTemplate,
				templateLock: false,
			}),
		),
	);
}

registerBlockType("pns/split-section", {
	icon: SPLIT_SECTION_ICON,
	edit: SplitSectionEdit,
	save() {
		return el(InnerBlocks.Content);
	},
	variations: [
		{
			name: "image",
			title: __("PNS - Split Section Image", "pns-blocks"),
			icon: SPLIT_IMAGE_ICON,
			description: __(
				"Two-column section with editable copy and a replaceable framed image.",
				"pns-blocks",
			),
			attributes: {
				align: "full",
				backgroundColor: "white",
				layoutVariant: "media-right",
				mediaType: "image",
			},
			innerBlocks: imageTemplate,
			isDefault: true,
			isActive: ["mediaType"],
			scope: ["block", "inserter"],
		},
		{
			name: "video",
			title: __("PNS - Split Section Video", "pns-blocks"),
			icon: SPLIT_VIDEO_ICON,
			description: __(
				"Two-column section with editable copy and a replaceable video file.",
				"pns-blocks",
			),
			attributes: {
				align: "full",
				backgroundColor: "white",
				layoutVariant: "media-right",
				mediaType: "video",
			},
			innerBlocks: videoTemplate,
			isActive: ["mediaType"],
			scope: ["block", "inserter"],
		},
		{
			name: "slideshow",
			title: __("PNS - Split Section Slideshow", "pns-blocks"),
			icon: SPLIT_SLIDESHOW_ICON,
			description: __(
				"Two-column section with editable copy and a framed Jetpack slideshow.",
				"pns-blocks",
			),
			attributes: {
				align: "full",
				backgroundColor: "white",
				layoutVariant: "media-right",
				mediaType: "slideshow",
			},
			innerBlocks: slideshowTemplate,
			isActive: ["mediaType"],
			scope: ["block", "inserter"],
		},
	],
});
