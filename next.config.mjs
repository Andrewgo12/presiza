/** @type {import('next').NextConfig} */
const nextConfig = {
  // Configure as SPA to maintain React Router DOM structure
  output: 'export',
  trailingSlash: true,
  skipTrailingSlashRedirect: true,
  distDir: 'dist',

  eslint: {
    ignoreDuringBuilds: true,
  },
  typescript: {
    ignoreBuildErrors: true,
  },
  images: {
    unoptimized: true,
  },

  // Configure for static export
  assetPrefix: process.env.NODE_ENV === 'production' ? '' : '',
}

export default nextConfig
