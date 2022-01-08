import { registerBlockType } from '@wordpress/blocks'
import metadata from '../block.json'
import save from './save'
import edit from './edit'

const settings = {
	edit,
	save
}

registerBlockType( metadata, settings )
