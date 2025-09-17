import * as React from "react"
import { cn } from "@/lib/utils"

type ButtonVariant = "default" | "destructive" | "outline" | "secondary" | "ghost" | "link"
type ButtonSize = "default" | "sm" | "lg" | "icon"

interface ButtonProps extends React.ComponentProps<"button"> {
  variant?: ButtonVariant
  size?: ButtonSize
}

function getButtonClasses(variant: ButtonVariant = "default", size: ButtonSize = "default") {
  const baseClasses = "btn d-inline-flex align-items-center justify-content-center gap-2 text-nowrap fw-medium"
  
  const variantClasses = {
    default: "btn-primary",
    destructive: "btn-danger",
    outline: "btn-outline-primary",
    secondary: "btn-secondary",
    ghost: "btn-light",
    link: "btn-link text-primary text-decoration-underline",
  }
  
  const sizeClasses = {
    default: "btn",
    sm: "btn-sm",
    lg: "btn-lg",
    icon: "btn p-2",
  }
  
  return `${baseClasses} ${variantClasses[variant]} ${sizeClasses[size]}`
}

function Button({
  className,
  variant = "default",
  size = "default",
  ...props
}: ButtonProps) {
  return (
    <button
      className={cn(getButtonClasses(variant, size), className)}
      {...props}
    />
  )
}

export { Button }
