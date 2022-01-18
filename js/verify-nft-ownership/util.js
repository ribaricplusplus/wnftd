import { ethers } from 'ethers';
import { dispatch } from '@wordpress/data';
import { STORE_NAME } from './store';

let provider = null;
let signer = null;

const chainNames = {
	1: 'Ethereum Mainnet',
	137: 'Polygon Mainnet',
};

export function getChainName( id ) {
	if ( ! chainNames[ id ] ) {
		throw new Error( 'Invalid chain name ID.' );
	}

	return chainNames[ id ];
}

export function hasMetaMask() {
	return window.ethereum !== undefined && window.ethereum.isMetaMask;
}

/**
 * Some error codes and needed additional data:
 *
 * - chain_id_mismatch
 *   - productChainId: number
 */
export function errorFactory( name, additionalData = {} ) {
	const error = new Error();
	error.code = name;
	error.additionalData = additionalData;
	return error;
}

export function getWeb3Provider() {
	if ( provider ) {
		return provider;
	}

	if ( ! window.ethereum ) {
		throw new Error();
	}

	provider = new ethers.providers.Web3Provider( window.ethereum );

	window.ethereum.on( 'accountsChanged', () => {
		window.location.reload();
	} );

	window.ethereum.on( 'chainChanged', () => {
		window.location.reload();
	} );

	return provider;
}

export async function getWeb3Signer() {
	if ( signer ) {
		return signer;
	}

	if ( ! provider ) {
		getWeb3Provider();
	}

	signer = await provider.getSigner();

	const productChainId = getChainId();
	const signerChainId = await signer.getChainId();

	if ( productChainId !== signerChainId ) {
		throw new errorFactory( 'chain_id_mismatch', {
			productChainId,
		} );
	}

	return signer;
}

export function getChainId() {
	if ( window?.wnftdData?.chainId === undefined ) {
		throw new Error( 'Missing chain id' );
	}

	if ( window.wnftdData.chainId === 0 ) {
		throw new Error( 'Invalid chain id' );
	}

	return window.wnftdData.chainId;
}

export async function getPublicAddress() {
	const signer = await getWeb3Signer();
	const publicAddress = ( await signer.getAddress() ).toLowerCase();
	return publicAddress;
}
