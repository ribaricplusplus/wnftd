import { useBlockProps } from '@wordpress/block-editor';
import HelloWorld from './block'

export default function Edit() {
	return (
		<div { ...useBlockProps({ className: 'ddlb-root' }) }>
			<HelloWorld />
		</div>
	)
}
