import { useBlockProps } from '@wordpress/block-editor';

export default function Save() {
	return <div { ...useBlockProps.save({ className: 'ddlb-root' }) }></div>
}
